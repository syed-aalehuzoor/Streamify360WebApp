from time import sleep
import uvicorn
from fastapi import FastAPI, APIRouter, HTTPException

from core import post_task, delete_video_by_id, delete_expired_videos, APP_URL, log_info, log_error
from requests import post as post_request
import threading
from apscheduler.schedulers.background import BackgroundScheduler
from datetime import timedelta, datetime
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

@router.post('/api/process_video/{id}')
def process_video(id: str):
    thread = threading.Thread(target=post_task, args=(id,))
    thread.start()
    return {"message": f"Video is being processed with id: {id}"}

@router.post('/api/delete_video/{id}')
def delete_video(id: str):
    thread = threading.Thread(target=delete_video_by_id, args=(id,))
    thread.start()
    return {"message": f"Video is being processed with id: {id}"}

manager = LaravelQueueManager()
def lifespan(_):
    scheduler = BackgroundScheduler()
    scheduler.add_job(manager.start_queue_worker, 'interval', seconds= 60 * 60, next_run_time=datetime.now())
    scheduler.add_job(cron_job, 'interval', seconds= 60 * 60, next_run_time=datetime.now())
    scheduler.start()
    print("startup")
    yield
    manager.stop_queue_worker()
    print("shutdown")


app = FastAPI(lifespan=lifespan)

app.include_router(router=router)


if __name__ == "__main__":
    uvicorn.run(app=app, host='127.0.0.1', port=5000)