import uvicorn
from fastapi import FastAPI, APIRouter, BackgroundTasks
from core import download_video, delete_video_by_id, delete_expired_videos, APP_URL, log_info, log_error
from requests import post as post_request
from apscheduler.schedulers.background import BackgroundScheduler
from datetime import datetime, timedelta
from Laravel import LaravelQueueManager

router = APIRouter()

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

@router.post('/api/download_video/{id}')
def process_video(id: str, backgroundtasks: BackgroundTasks):
    backgroundtasks.add_task(download_video, id)
    return {"message": f"Video is being processed with id: {id}"}

@router.post('/api/delete_video/{id}')
def delete_video(id: str, backgroundtasks: BackgroundTasks):
    backgroundtasks.add_task(delete_video_by_id, id)
    return {"message": f"Video is being processed with id: {id}"}

def add_periodic_job(scheduler, job_function, interval_seconds):
    scheduler.add_job(job_function, 'interval', seconds=interval_seconds, next_run_time=datetime.now()+timedelta(seconds=30))

def setup_scheduler():
    manager = LaravelQueueManager()
    scheduler = BackgroundScheduler()
    add_periodic_job(scheduler, manager.start_queue_worker, 3600)  # 1 hour
    #add_periodic_job(scheduler, cron_job, 3600)  # 1 hour
    scheduler.start()
    return scheduler, manager

def lifespan(_):
    scheduler, manager = setup_scheduler()
    yield
    scheduler.shutdown()
    manager.stop_queue_worker()

app = FastAPI(lifespan=lifespan)

app.include_router(router=router)

if __name__ == "__main__":
    uvicorn.run(app=app, host='127.0.0.1', port=5000)