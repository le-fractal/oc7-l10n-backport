#!/bin/bash

# the name of the new branch where your patch will be
branch_name="l10n-fr-backport"
# comment for the commit
commit_comment="patched translation (backport from master)" 
# where your patched code resides
patched_dir="/home/zinks/src/core7c"

declare -a apps=("" "activity" "bookmarks" "calendar" "contacts" "documents" "files" "files_encryption" "files_external" "files_pdfviewer" "files_sharing" "files_texteditor" "files_trashbin" "files_versions" "firstrunwizard" "gallery" "search_lucene" "templateeditor" "updater" "user_ldap" "user_webdavauth")

for app in "${apps[@]}"
do
	cd "$patched_dir/apps/$app"
	git checkout -b $branch_name
	git commit -a -m "$commit_comment"
	#use something similar to this line if you wanted to create patch files too
	#git show HEAD > "/some/directory/$app.patch"
	git push mine $branch_name 
done

