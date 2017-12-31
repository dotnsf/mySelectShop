# mySelectShop

## Overview

Sample Web Shop with Amazon Affiliate

## Minimum requirement

- IBM Cloud( http://bluemix.net/ ) account

## Prefered requirement

- PHP runtime

    - If you want to customize items DB, you need PHP runtime.

- Cloudant NoSQL database service

    - https://cloudant.com/

    - Cloudant username & password

- cf cli tool

    - https://github.com/cloudfoundry/cli/releases


## Advanced requirement

- Amazon Associate acount

    - for affilicate enabled.

- Amazon Web Service acount

    - for custom items crawling.

## Simplest web application install

- Login to IBM Cloud at first: http://bluemix.net/

- Folk this repository into yours.

- Edit manifest.yml name, host, and domain as needed.

- Click this button to deploy PHP application server to IBM Cloud:

[![Deploy to Bluemix](https://bluemix.net/deploy/button.png)](https://bluemix.net/deploy?repository=https://github.com/dotnsf/mySelectShop)

    - Edit application name, if needed, before deployment.

- Create IBM Cloudant NoSQL DB service, and bind it to application.

## Optional Install

- If you have Amazon Associate account tag, edit credentials.php with it.

- Deploy application again, or run following command:

    - cf push [appname]

## Load sample

- Git clone or Download this repository.

- Login to IBM Cloud, and create IBM Cloudant service instance, if you don't have one yet.

- Check your connection credentials of IBM Cloudant. You need username and password later.

- Edit common.php with your Cloudant username & password

- Load sample items record again:

`$ php -f bulkload.php [items.txt] [u]`

    - You can specify input file name.

        - If not specified, bulkload.js will use items.txt.

    - You can also specify update mode.

        - If specified, bulkload.php will delete and insert items.

        - If not specified, bulkload.php will just append items to current db.

- There are some sample items files:

    - items.txt:

        * Beauty: Skincare, Haircare, Bodycare, and Mens Beauty

    - items.json.txt.15335631

        * Sports: Boxing

    - items.json.txt.344932011

        * Sports: Exercise Goods

    - items.json.txt.52912051

        * Beauty: Mens Beauty


## How to use web application

- Deploy application into IBM Cloud,

`$ cf login -a https://api.ng.bluemix.net/` (in case you use USA-south region)

`$ cf push appname`

- or Click this button to deploy to IBM Cloud:

[![Deploy to Bluemix](https://bluemix.net/deploy/button.png)](https://bluemix.net/deploy?repository=https://github.com/dotnsf/mySelectShop)


- Browse your application.

- Enjoy!


## Customize your items

If you don't want to use sample data, you can follow these instructions:

- Open crawl.php, and edit parameter lines:

    - $nodes:

        * crawler will search items with specified category(s).

        * See https://affiliate.amazon.co.jp/gp/associates/help/t100 for details.

- Run following command:

`$ php -f crawl.php [items.txt]`

    - You can specify output file name.

    - If not, crawl.php will use items.txt.

- Load new sample file.

`$ php -f bulkload.php [items.txt]`

    - You can specify input file name.

    - If not, bulkload.php will use items.txt.

## (Option)How to create input file manually

You can create input file for bulkload.php manually.

bulkload.js would expect following text file:

`{"code":"JAN/EAN/UTC code 1", "name":"Item name 1", "price": 100, "maker": "Maker of item 1", "brand": "Brand of item 1", "image_url": "URL of item 1 image", "asin": "ASIN code of item 1"}

{"code":"JAN/EAN/UTC code 2", "name":"Item name 2", "price": 200, "maker": "Maker of item 2", "brand": "Brand of item 2", "image_url": "URL of item 2 image", "asin": "ASIN code of item 2"}

  :

  :`

You would find it, but they are NOT JSON file. Each line are JSON, but they are just a collection of JSON(not JSON array).


## (Option)How to get Amazon Associate ID(tag)

You can affiliate your shop with them. Refer following page:

http://dotnsf.blog.jp/archives/1062052263.html

## (Option)How to get Amazon Product Advertisement API key/secret

You can crawl and collect your prefered items with them. Refer following page:

http://dotnsf.blog.jp/archives/1064227473.html

## Licensing

This code islicensed under MIT.


## Copyright

2017 K.Kimura @ Juge.Me all rights reserved.
