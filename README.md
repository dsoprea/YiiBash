Dependencies
------------

* php5-cgi

Warnings
--------

* The command that you're doing completion on ("yiic", for example) must be in your path. Normally, Yii works fine this way. Just make sure your application uses dirname(__FILE__) whenever it needs to locate files within the project.

* There must be a separate instance of the bash-completion script stub for each project that you want to have completion for. By default, Yii's commands are launched via "yiic". You will have to rename this file (or symlink it from something else), and then modify each of the bash-completion stubs.

* Currently, the command and command-action names will autocomplete, but only the first parameter will autocomplete.

Instructions
------------

* Add the Bash auto-complete stub to "/etc/bash_completion.d" for PHP Yii projects. Make sure to set YII_APP_ROOT at the top.
* Add "yiic-cgi" to your Yii project root.
* Add "BashCompleteCommand.php" to the protected/commands/ directory in your Yii project.

