#!/bin/bash

# where owncloud stable7 code will reside
checkout_dir=/home/zinks/src/core7c

# repositories parent url
# probably your github URL as you want to make PRs from your forks
repo_url=https://github.com/zinks-

declare -a apps=("activity" "bookmarks" "calendar" "contacts" "documents" "files" "files_encryption" "files_external" "files_pdfviewer" "files_sharing" "files_texteditor" "files_trashbin" "files_versions" "firstrunwizard" "gallery" "search_lucene" "templateeditor" "updater" "user_ldap" "user_webdavauth")

git clone $repo_url/core.git --branch stable7 --single-branch "$checkout_dir"
cd "$checkout_dir"
git remote rename origin mine

for app in "${apps[@]}"
do
	if [ ! -d "$checkout_dir/apps/$app" ]; then
		git clone $repo_url/$app.git --branch stable7 --single-branch "$checkout_dir/apps/$app"
		cd "$checkout_dir/apps/$app"
		git remote rename origin mine
	fi
done

