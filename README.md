# BSUIR bot for Telegram #

Bot for BSUIR's students. Display schedule by date.

### Based on ###

* BSUIR's schedule API.

### Set it up ###

* Rent droplet, domain and SSL-certificate for WebHook;
* Install redis server and phpredis extension. 
* Upload repo in private folder, disallow it for bots in robots.txt;
* Register bot via @BotFather in Telegram;
* Insert token in *Bot* class init (var $bot);
* Set Telegram API's Webhook;
* Enjoy your bot.

### Some info ###

* ~~Not require database, use json for store data~~ Now use redis (json fix coming soon);
* Latest version 0.5;
* Demo available [@BSUIRBot] (https://telegram.me/BSUIRbot)