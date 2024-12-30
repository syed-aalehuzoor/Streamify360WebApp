from time import sleep
from flask import Flask, request, jsonify
from core import post_task, delete_video_by_id, delete_expired_videos, APP_URL, log_info, log_error
from requests import post as post_request
import threading
from apscheduler.schedulers.background import BackgroundScheduler
app = Flask(__name__)
from datetime import timedelta, datetime
from LaravelQueue import LaravelQueueManager

def cron_job():
    """This is the cron job function."""
    log_info("Running cron job...")
    print("Running cron job...")
    try:
        cleanup_endpoint = f"{APP_URL}/api/cleanup"
        refresh_analytics_endpoint = f"{APP_URL}/api/refresh-analytics"
        if post_request(cleanup_endpoint, data={}).status_code != 200:
            log_error("Failed to get a valid response for cleanup.")

        # Refresh analytics task
        if post_request(refresh_analytics_endpoint, data={}).status_code != 200:
            log_error("Failed to get a valid response for refresh analytics.")
        else:
            log_info("Cron job completed.")
            print("Cron job completed.")
        delete_expired_videos()
    except Exception as e:
        log_error(f"Error occurred in cron job: {e}")

@app.route('/api/process_video/<id>', methods=['POST'])
def process_video(id):
    thread = threading.Thread(target=post_task, args=(id,))
    thread.start()
    result = {"message": f"Video is being processed with id: {id}"}
    return jsonify(result)

@app.route('/api/delete_video/<id>', methods=['POST'])
def delete_video(id):
    thread = threading.Thread(target=delete_video_by_id, args=(id,))
    thread.start()
    result = {"message": f"Video is being processed with id: {id}"}
    return jsonify(result)

manager = LaravelQueueManager()



if __name__ == "__main__":
    scheduler = BackgroundScheduler()
    try:
        cron_job()
        scheduler.add_job(manager.start_queue_worker, 'interval', seconds= 60 * 60, next_run_time=datetime.now() + timedelta(seconds=5))
        scheduler.add_job(cron_job, 'interval', seconds= 60 * 60)
        scheduler.start()
        app.run(host='127.0.0.1', port=5000, debug=True)
    except KeyboardInterrupt:
        print("Stopping...")
        manager.stop_queue_worker()
        scheduler.shutdown()
        print("Service stopped.")