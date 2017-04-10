# some more ls aliases
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi

export PS1='\[\033[0;31m\]\[\033[0m\]\[\033[01;31m\]docker-\u\[\033[01;33m\]@\[\033[01;36m\]''\[\033[01;33m\]\w \[\033[01;35m\]\[\033[00m\]# '
