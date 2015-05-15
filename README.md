### GoCardless PHP Client Demo App

This is a small demo application utilizing the GoCardless PHP client to list customers, create mandates, view mandates, and list payments.

The code interfacing with the GoCardless api exists in `gocardless.php` and some actions are within the `router.php` file also in the root directory. This example uses the builtin PHP webserver by default, but you can use any normal PHP server as well.

To get started get your API Token, then run `GC_TOKEN=YOUR_TOKEN make server` to install dependencies and start the server. The makefile should take care of everything if you're running on osx or linux with PHP >=5.3.3 installed.
