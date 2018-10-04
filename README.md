# BSUIR bot for Telegram #

[![Code Climate](https://codeclimate.com/github/Nekaravaev/BSUIRBot/badges/gpa.svg)](https://codeclimate.com/github/Nekaravaev/BSUIRBot)
[![Build Status](https://travis-ci.org/Nekaravaev/BSUIRBot.svg?branch=master)](https://travis-ci.org/Nekaravaev/BSUIRBot)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Nekaravaev/BSUIRBot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Nekaravaev/BSUIRBot/?branch=master)


Bot for BSUIR's students. Display schedule by date.

### Based on ###

* BSUIR's schedule API.

### Set it up ###

* Rent droplet, domain and SSL-certificate for WebHook;
* Install redis server and phpredis extension. 
* Upload repo in private folder, disallow it for bots in robots.txt;
* Register bot via @BotFather in Telegram;
* Fill up app/Config.php.example class and remove "example" extension;
* Set Telegram API's Webhook;
* Enjoy your bot.

### Some info ###

* Now use redis;
* Latest version 1.0;
* Demo available [@BSUIRBot] (https://telegram.me/BSUIRbot)

### RoadMap ###
* fix VK bot
* fix picking day and week number by date
* move all texts to phrases