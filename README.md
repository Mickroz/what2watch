## what2watch
This script was created because i always had to check which episodes i had watched on trakt,
and then see if SickBeard downloaded the next episode, and see if Auto-Sub downloaded the subs for it so i could watch it.
With this script it collects all shows in Sickbeard, checks if there are any downloaded episodes for it, if so,
the script will check to see what episode is next in your progress on trakt.
If those 2 match, the script will check if there are any subs downloaded for it and if true, show the episode in the overview,
if not, it will skip the show and continue with the next show.
This scripts has to be run on the machine where you have your downloaded shows.

## Issues
This script relies heavily on api calls to SickBeard and Trakt.tv, therefor it could take a while to finish if you have a lot of shows in SickBeard.
Therefor it will cache the created array for a half hour (3600) to speed things up a bit if you want to check it later.

The second issue is that because of the open_basedir setting in php, if you get this error, you have to add the location where you store your shows to php.ini.
Read more on this on [php.net](http://php.net/manual/en/ini.core.php#ini.open-basedir) and [here](http://kb.mediatemple.net/questions/514/How+do+I+set+the+path+for+open_basedir%3F#gs) on how to add the location.

## Todo
- [x] Add some settings to config.php
- [ ] Grab naming pattern from SickBeard
- [ ] Move subs extension to config for user configurable setting
- [ ] Save banner on disk for later usage?

thanks to @derkens for testing :grin:
