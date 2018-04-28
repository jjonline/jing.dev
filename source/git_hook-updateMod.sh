#!/bin/sh
#
# --- Command line
refname="$1"
oldrev="$2"
newrev="$3"
DevelopPath="/www/wwwroot/test.laike188.com/"
BetaPath="/www/wwwroot/beta.laike188.com/"

# --- Safety check
#if [ -z "$GIT_DIR" ]; then
#   echo "Don't run this script from the command line." >&2
#   echo " (if you want, you could supply GIT_DIR then run" >&2
#   echo "  $0 <ref> <oldrev> <newrev>)" >&2
#   exit 1
#fi

# --- Deploy Project Depend branch
case $refname in
   "refs/heads/master")
      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Beta Environments Start.\033[32m [ok] \033[0m"

      ## check project dir
      if [ ! -d "$BetaPath" ];then
            ## make dir
            mkdir $BetaPath

            ## git clone project
            git clone /opt/productive $BetaPath > /dev/null 2>&1
      fi

      ## enter new project dir 
      cd $BetaPath

      ## checkout master code
      git checkout master > /dev/null 2>&1

      ## update code
      git pull > /dev/null 2>&1

      ## modify config file[Force Copy environments file]
      /bin/cp -rf "$BetaPath"environments/test/* "$BetaPath"/config/
     
      ## reload php-fpm 
      service php71-fpm reload > /dev/null 2>&1

      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Beta Environments Success. \033[32m [ok] \033[0m"
      ;;
   "refs/heads/develop")
      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Test Environments Start.\033[32m [ok] \033[0m"

      ## check project dir
      if [ ! -d "$DevelopPath" ];then
          ## make dir
          mkdir $DevelopPath

          ## git clone project
          git clone /opt/productive $DevelopPath > /dev/null 2>&1
      fi

      ## enter new project dir
      cd $DevelopPath

      ## checkout master code
      git checkout develop > /dev/null 2>&1

      ## update code
      git pull > /dev/null 2>&1

      ## modify config file[Force Copy environments file]
      /bin/cp -rf "$DevelopPath"environments/test/* "$DevelopPath"/config/
      
      ## reload php-fpm
      service php71-fpm reload > /dev/null 2>&1

      ## reload tasks services
      # systemctl reload tasks.service> /dev/null 2>&1

      ## kill tasks
      kill -9 `cat /www/wwwroot/test.laike188.com/runtime/swoole.pid`

      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Test Environments Success.\033[32m [ok] \033[0m"

      ;;
   *)
      ## Clear Beta Cache
      cd $BetaPath
      php think clear > /dev/null 2>&1
     
      ## Clear Test Cache
      cd $DevelopPath
      php think clear > /dev/null 2>&1

      ## reload php-fpm
      service php71-fpm reload > /dev/null 2>&1
     
      echo -e "[`date -d today +"%k:%M:%S.%N"`]Clear Cache And Reload PHP-FPM Success Finished.\033[32m [ok] \033[0m"
   ;;
esac

echo -e "[`date -d today +"%k:%M:%S.%N"`]Task Success Finished.\033[32m [ok] \033[0m" 

## Finished
exit 0