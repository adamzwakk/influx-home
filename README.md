# Influx Home

Just a bunch of scripts to insert data into a local InfluxDB instance for home monitoring. I've tried to make it modular/generic enough that anyone can throw their vars/settings in, and away you go.

Currently supports:
- Nest (Home thermostat reading)
- OpenWeatherMap (local weather/temperature)
- Philips HUE (# of lights on)
- Transmission (upload/download bandwidth)
- Sabnzbd (download bandwidth)

To get started:

1. Run `composer install` to get all dependancies
2. Copy the `.env.example` to `.env` and fill our your own keys/ids
3. Run `php start.php` to run the script once, or set `php cron.php` to run every minute to fill in data automatically (I'm tuning each service to adhere to their rate limits etc)
4. Use grafana (or any influx compatible graphing app) and look at your fancy data/graphs

TODO:
- Water Usage Monitoring
- Power monitoring
- Program/Console monitoring (what do I use when and why, using when I'm signed into X service)
- ????
