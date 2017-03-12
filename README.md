# Random Events
Randomly reply to threads with customized messages!

## Features
* Configure the user ID of the account used to post random events
* Choose which forums and usergroups you want to be eligible for random events
* Choose how frequent you want random events to be posted
* Define your own random events -- supports MyCode/BBCode!

## Installation
* Simply copy inc/plugins/randomevents.php to your site's inc/plugins folder.
* Then install and activate the plugin via your Admin CP.
* Configure the settings to your desire via Admin CP > Configuration > Random Events Settings.

# Technical
* A random number is generated every time a user posts. If this number meets the frequency setting, further checks will be made to determine if
  1. The post is in an eligible forum, per the settings
  2. The user is in an eligible usergroup, per the settings
* Determining whether the user is in a valid usergroup requires a database query—thus, this check is performed last to minimize database calls.
* A second random number is then generated to choose a random event from the settings.
* A post is then generated at TIME_NOW+1—that is, one millisecond after the original post is created.
