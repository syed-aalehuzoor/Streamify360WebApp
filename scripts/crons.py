import subprocess
from time import sleep

while True:
    subprocess.run(["php", "../artisan", "schedule:run"])
    sleep( 60 )