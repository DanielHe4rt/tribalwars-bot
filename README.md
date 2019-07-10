

<h1 align="center">
  Tribal Wars PHP Bot
</h1>

<h3 align="center">
    A bot for the TribalWars game made with Lumen Framework.
</h3> 

## :exclamation: The concept of everything

I was thinking about going back to playing Tribal Wars after a few years without playing. But in those years that have passed, I learned to program. And if there's something I like to do, it's BOTS!<br>
The idea is to have the manual mode (where the player can execute all the actions via console) and the automatic mode (where a list of commands will be created to be executed every hour to build, recruit, farm and etc)

## :computer: Technologies

I chose to use Lumen because I work with the framework on a daily basis, so it makes a lot easier. Here is the most important about the code: 

- Artisan Console<br>
Used to make the interface and display all the game data on the console. Basically the I/O of the application.
- Guzzle<br>
All the requests to the TribalWars API/Website are made with GuzzleHTTP, because is the most simple and organized PHP HTTP client which i know until now.

Ok, now let's install and try it.

## :rocket: 5 minutes quick start

:bulb: After your project is cloned, you must have installed the composer on your machine to download all dependencies.
Entering the command below will begin to download the dependencies:

```
composer install
```
<br>
With everything set up, you can run the game using the command below (Remember to use this command on the project folder):

```
php artisan game:start
```

<p>Running the command, you will be asked for your game credencials:</p>

<img src="https://i.imgur.com/mU6r1Ll.png"><br>


<p>Now you need to choose which "World" you want to connect:</p>

<img src="https://i.imgur.com/heYYMyc.png"><br>

<p>PS: Worlds with "JOIN" means you already have an village created.</p>

<p>After the world is selected, you will see your entire village information inside the console.</p>


<img src="https://i.imgur.com/9TNgSsk.png"><br>

<p>This bot will only work on the brazilian server for now because there is a lot of stuff to do before I release something useful for the global players.</p>



## :mailbox_with_mail: License 

This software was created for study purposes only. Feel free to try it out.



