#!/bin/sh
#
# --- Command line
refname="$1"
oldrev="$2"
newrev="$3"
DevelopPath="/www/wwwroot/test.laike188.com/"
BetaPath="/www/wwwroot/beta.laike188.com/"
DevelopBackPath="/www/wwwroot/_test.laike188.com/"
BetaBackPath="/www/wwwroot/_beta.laike188.com/"

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

      ## check back dir exist && delete back dir
      if [ -d "$BetaBackPath" ];then
            rm -rf $BetaBackPath
      fi
      ## move project dir
      if [ ! -d "$BetaPath" ];then
            mkdir $BetaPath
            mkdir "$BetaPath"manage/uploads
      else
            mv $BetaPath $BetaBackPath
      fi

      ## make new project dir
      mkdir $BetaPath

      ## enter new project dir 
      cd $BetaPath

      ## git clone project
      git clone /opt/productive $BetaPath > /dev/null 2>&1

      ## checkou master code
      git checkout master > /dev/null 2>&1

      ## copy all uploads dir
      /bin/cp -rf "$BetaBackPath"manage/uploads/ "$BetaPath"manage/

      ## modify config file[Force Copy environments file]
      /bin/cp -rf "$BetaPath"environments/beta/* "$BetaPath"/config/

      ## delete back dir
      if [ -d "$BetaBackPath" ];then
            rm -rf $BetaBackPath
      fi
     
      ## reload php-fpm 
      service php71-fpm reload > /dev/null 2>&1

      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Beta Environments Success. \033[32m [ok] \033[0m"
      ;;
   "refs/heads/develop")
      echo -e "[`date -d today +"%k:%M:%S.%N"`]Publish Test Environments Start.\033[32m [ok] \033[0m"

      ## check back dir exist && delete back dir
      if [ -d "$DevelopBackPath" ];then
            rm -rf $DevelopBackPath
      fi
      ## move project dir OR make back dir
      if [ ! -d "$DevelopPath" ];then
            mkdir $DevelopPath
            mkdir "$DevelopPath"manage/uploads
      else
            mv $DevelopPath $DevelopBackPath
      fi

      ## make new project dir
      mkdir $DevelopPath

      ## enter new project dir 
      cd $DevelopPath

      ## git clone project
      git clone /opt/productive $DevelopPath > /dev/null 2>&1

      ## checkou master code
      git checkout master > /dev/null 2>&1

      ## copy all uploads dir
      /bin/cp -rf "$DevelopBackPath"manage/uploads "$DevelopPath"manage/

      ## modify config file[Force Copy environments file]
      /bin/cp -rf "$DevelopPath"/environments/test/* "$DevelopPath"/config/

      ## delete back dir
      if [ -d "$DevelopBackPath" ];then
            rm -rf $DevelopBackPath
      fi
      
      ## reload php-fpm
      service php71-fpm reload > /dev/null 2>&1

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