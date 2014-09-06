# eecli command completion

_eecli_command_list () {
    eecli --no-ansi | sed "1,/Available commands/d" | awk '/^  [a-z]+/ { print $1 }'
}

_eecli () {
  compadd `_eecli_command_list`
}

compdef _eecli eecli
