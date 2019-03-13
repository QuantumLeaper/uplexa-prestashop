# uPlexa PrestaShop
A Prestashop addon for accepting uPlexa (UPX)

Compatible with the stable version of Prestashop (1.6.x). And working on 1.7.x

## Dependencies
This plugin is rather simple but there are a few things that need to be set up beforehand.

* A web server! Ideally with the most recent versions of PHP and mysql

* A uPlexa wallet. You can find the official wallet [here](https://uplexa.com/downloads)

* [Prestashop](https://prestashop.com)
Prestashop is open source e-commerce engine to run your own shop and this uPlexa addon

## Step 1: Activating the plugin
* Downloading: First of all, you will need to download the module. You can download the latest release as a .zip file from https://github.com/uplexa/uplexa-prestashop If you wish, you can also download the latest source code from GitHub. This can be done with the command `git clone https://github.com/uplexa/uplexa-prestashop.git` or can be downloaded as a zip file from the GitHub web page.

* Unzip the file uplexaps-master.zip if you downloaded the zip from the master page [here](https://github.com/uplexa/uplexa-prestashop).

* Upload the module and activate it. You can refer the official documentation [here](https://addons.prestashop.com/en/content/21-how-to)

## Step 2 : Use your wallet address and connect to a uPlexa daemon

### Option 1: Running a full node yourself

To do this: start the uPlexa daemon on your server and leave it running in the background. This can be accomplished by running `./uplexad` inside your uPlexa downloads folder. The first time that you start your node, the uPlexa daemon will download and sync the entire uPlexa blockchain. This can take several hours and is best done on a machine with at least 4GB of ram, an SSD hard drive (with at least 40GB of free space), and a high speed internet connection.
You can refer the official documentation for running full node from [here](https://github.com/uplexa/uplexa).

### Option 2: Connecting to a remote node
Use a remote node to connect, remote.uplexa.com:21061 will automatically connect you to a random uplexa node.

`Note: You must run your JSON RPC on the host server of Prestashop against your wallet`

### Setup your uPlexa wallet-rpc

* Setup a uPlexa wallet using the uplexa-wallet-cli tool. If you do not know how to do this you can learn about it at [https://github.com/uplexa/uplexa](https://github.com/uplexa/uplexa)



* Start the Wallet RPC and leave it running in the background. This can be accomplished by running `uplexa-wallet-rpc --wallet-file /path/to/wallet/file --password walletPassword --rpc-bind-port 21065 --disable-rpc-login` where "/path/to/wallet/file" is the wallet file for your uPlexa wallet. If you wish to use a remote node you can add the `--daemon-address` flag followed by the address of the node. `--daemon-address remote.uplexa.com:21061` for example.

## Step 4: Setup uPlexa Gateway in Prestashop
* Navigate to the "Modules and Services" panel in the Prestashop sidebar and identify `uPlexa Payments` module and click on `configure`.
* Update `uPlexa Wallet Address` and `Wallet RPC IP/HOST`
* Note: Wallet RPC IP should start with the protocol and end with port address. `Eg. http://127.0.0.1:21065`
* Save the changes and you are good to go.
