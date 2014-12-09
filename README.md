oc7-l10n-backport
=================

The translations for the ownCloud webapp are not automatically updated from one minor version to the next. This tool compares translations from ownCloud 7 and 8 and updates the v7 translations to be like the new ones. Then you only need to make a PR to the stable7 branch to have the next minor version of ownCloud with updated translations.

Getting Ready
-------------

1. Get a copy of the current ownCloud code. An easy way to get it is to download the daily snapshot https://download.owncloud.org/community/daily/owncloud-daily-master.tar.bz2 and uncompress the files to your disk.
2. On github, fork the core repository and the repos of the apps that are shipped with oc7 (don't rename them)
3. Use the `clonestable7.sh` script (see below) to checkout the stable7 branch of your forks to your hard drive

Getting the stable7 code with clonestable7.sh
---------------------------------------------

1. Open the `clonestable7.sh` file with your favorite editor
2. Set `checkout_dir` to the root directory where you want to put the stable7 code. For example if you set `checkout_dir=/home/john/src/stable7`, the core code will be checked out in `/home/john/src/stable7`, the code for the bookmarks apps will be checked out in `/home/john/src/stable7/apps/bookmarks`, etc. The top folder will be created.
3. Set `repo_url` to your github URL. If you set it to `repo_url=https://github.com/john`, it will look for your fork of the core code in `https://github.com/john/core.git`, etc.
4. Save the file
5. Execute `bash clonestable7.sh`


Using backporter.php to patch translations
------------------------------------------

1. Open the `backporter.php`file with your favorite text editor
2. Set the `OC7_CORE_DIR` constant to the path where your stable7 code is (it's the same directory as `checkout_dir` from the the `clonestable7.sh` file)
3. Set the `OC8_CORE_DIR` constant to the path where you have uncompressed the daily snapshot from the 'Getting Ready' section
4. Set the `Z_LANGUAGE` constant to the code for the language you are trying to patch. 'fr' for French, 'de' for German, 'es' for Spanish, etc.
5. Save the file and exit the editor
6. Execute `php backporter.php` **Note:** nothing will be written to disk unless you pass the 'write' argument to the script. This is a dry-run test.

If the script finds any translations that have changed between oc7 and oc8 (master), it will output for each of them:
1. > The file where the original translation was found
2. -- The old translation (the one in oc7)
3. ++ The new translation (the one in oc8/master)

For example if I run it for the 'de' language (German), here's the output :

```
> File /home/zinks/src/core7c/settings/l10n/de.php line 122
-- "Use system's cron service to call the cron.php file every 15 minutes." => "Benutze den System-Crondienst um die cron.php alle 15 Minuten aufzurufen.",
++ "Use system's cron service to call the cron.php file every 15 minutes." => "Benutzen Sie den System-Crondienst, um die cron.php alle 15 Minuten aufzurufen.",
> File /home/zinks/src/core7c/core/l10n/de.php line 171
-- "Only %s is available." => "Es sind nur %s verfügbar.",
++ "Only %s is available." => "Es ist nur %s verfügbar.",
> File /home/zinks/src/core7c/apps/calendar/l10n/de.php line 97
-- "You cannot add non-public events to a shared calendar." => "Du kannst einem freigegeben Kalender nicht zu einem nicht-öffentlichen Kalender hinzufügen.",
++ "You cannot add non-public events to a shared calendar." => "Du kannst nicht-öffentliche Termine nicht zu einem öffentlichen Kalender hinzufügen.",
> File /home/zinks/src/core7c/apps/files_encryption/l10n/de.php line 18
-- "Initial encryption running... Please try again later." => "Initiale Verschlüsselung läuft... Bitte versuche es später wieder.",
++ "Initial encryption running... Please try again later." => "Anfangsverschlüsselung läuft … Bitte versuche es später wieder.",
> File /home/zinks/src/core7c/apps/files_encryption/l10n/de.php line 19
-- "Go directly to your %spersonal settings%s." => "Wechsle direkt zu Deinen %spersonal settings%s.",
++ "Go directly to your %spersonal settings%s." => "Direkt zu Deinen %spersonal settings%s wechseln.",
> File /home/zinks/src/core7c/apps/updater/l10n/de.php line 13
-- "All done. Click to the link below to start database upgrade." => "Alles erledigt. Klicke auf den unteren Link um die Datenbank-Hochrüstung zu starten.",
++ "All done. Click to the link below to start database upgrade." => "Alles erledigt. Bitte auf den unteren Link klicken, um das Datenbank-Upgrade zu starten.",
> File /home/zinks/src/core7c/apps/updater/l10n/de.php line 17
-- "Updater" => "Updater",
++ "Updater" => "Aktualisierer",
> File /home/zinks/src/core7c/apps/updater/l10n/de.php line 21
-- "Backup directory" => "Backup-Verzeichnis",
++ "Backup directory" => "Sicherungverzeichnis",
> File /home/zinks/src/core7c/apps/updater/l10n/de.php line 22
-- "Backup" => "Backup",
++ "Backup" => "Sicherung",
> File /home/zinks/src/core7c/apps/updater/l10n/de.php line 26
-- "No backups found" => "Keine Backups gefunden"
++ "No backups found" => "Keine Sicherungen gefunden",

===
10 strings have been found and patched
```

Don't be fooled by the fact that the script tells you at the end that it has patched anything. It hasn't because we didn't use the 'write' parameter when we called the script.

If the output looks right to you, you can call `php backporter.php write` to have the translations files overwritten with their updated version. Check the output to see if the script had troubles overwriting any of the files.

Assuming everything went well, all we need now is to get those babies on github so that we can make pull requests!

Commit the updated files and push them to your github forks using patchstable7.sh
---------------------------------------------------------------------------------

1. Open `patch7stable.sh` with your text editor
2. Set `branch_name` to a name you chose for your new branch: the one that will hold the updates. I recommend `l10n-<language code>-backport`
2. Set `commit_comment` to what you want the... commit comment to be.
3. Set `patched_dir` to the path of the directory that hold the stable7 code we just patched. It's again the same directory we encountered in the two other files' variables.
4. Execute `bash patchstable7.sh`. It will push all your changes to your forks.

**Note:** If you don't want to have to enter your password for each push, set up password cache as seen here: https://help.github.com/articles/caching-your-github-password-in-git/

Make Pull Requests
------------------

On github and for each repository, compare ownCloud's stable7 branch with your fork's l10n-xx-backport (or whatever you called it) branch and make a pull request.
