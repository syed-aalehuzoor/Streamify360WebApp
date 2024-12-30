import psutil
from psutil import NoSuchProcess
from subprocess import run as run_cmd, Popen, PIPE, CalledProcessError, TimeoutExpired

class LaravelQueueManager:
    def __init__(self):
        self.queue_worker_process = None

    @staticmethod
    def find_queue_worker():
        """
        Check if a Laravel queue worker is running.
        :return: PID of the running worker if found, else None.
        """
        for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
            try:
                if (
                    'php' in proc.info['name'] and
                    'artisan' in proc.info['cmdline'] and
                    'queue:work' in proc.info['cmdline']
                ):
                    return proc.info['pid']
            except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                continue
        return None

    def start_queue_worker(self):
        """
        Start the Laravel queue worker if not already running.
        """
        existing_pid = self.find_queue_worker()

        if existing_pid:
            print(f"Laravel queue worker is already running with PID {existing_pid}.")
            self.attach_to_worker(existing_pid)
        else:
            try:
                self.queue_worker_process = Popen(['php', 'artisan', 'queue:work'])
                print("Laravel queue worker started successfully.")
            except Exception as e:
                print(f"Failed to start Laravel queue worker: {e}")

    def attach_to_worker(self, pid):
        """
        Attach to a running Laravel queue worker process by PID.
        """
        try:
            self.queue_worker_process = psutil.Process(pid)
            print(f"Attached to Laravel queue worker with PID {pid}.")
        except psutil.NoSuchProcess:
            print("Error: The process no longer exists.")

    def stop_queue_worker(self):
        """
        Stop the Laravel queue worker process if it's running.
        """
        if isinstance(self.queue_worker_process, Popen):
            try:
                self.queue_worker_process.terminate()
                self.queue_worker_process.wait(timeout=5)
                print("Laravel queue worker terminated.")
            except TimeoutExpired:
                print("Laravel queue worker did not terminate in time.")
                self.queue_worker_process.kill()
            except Exception as e:
                print(f"Error stopping Laravel queue worker: {e}")
            finally:
                self.queue_worker_process = None
        elif isinstance(self.queue_worker_process, psutil.Process):
            try:
                if self.queue_worker_process.is_running():
                    self.queue_worker_process.terminate()
                    self.queue_worker_process.wait(timeout=5)
                    print("Laravel queue worker terminated.")
            except NoSuchProcess:
                print("Process does not exist anymore.")
            except Exception as e:
                print(f"Error stopping Laravel queue worker: {e}")
            finally:
                self.queue_worker_process = None
        else:
            print("No Laravel queue worker process is being tracked.")

    def status(self):
        """
        Check the current status of the queue worker.
        :return: A status message.
        """
        pid = self.find_queue_worker()
        if pid:
            return f"Laravel queue worker is running with PID {pid}."
        return "No Laravel queue worker is running."
