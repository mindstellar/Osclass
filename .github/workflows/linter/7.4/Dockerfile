FROM php:7.4

LABEL "com.github.actions.name"="PHP Syntax check"
LABEL "com.github.actions.description"="Run the native PHP linter"
LABEL "com.github.actions.icon"="check"
LABEL "com.github.actions.color"="blue"

ADD entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
