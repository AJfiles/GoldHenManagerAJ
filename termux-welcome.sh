#!/data/data/com.termux/files/usr/bin/bash

# Pantalla de bienvenida al abrir una sesión interactiva de Termux.
[[ -t 0 ]] || exit 0

CYAN='\033[1;36m'
VIOLET='\033[1;35m'
GOLD='\033[1;33m'
WHITE='\033[1;37m'
MUTED='\033[0;90m'
GREEN='\033[1;32m'
RED='\033[1;31m'
RESET='\033[0m'

clear
printf "${CYAN}╭──────────────────────────────────────────────────────╮${RESET}\n"
printf "${CYAN}│${RESET}                                                      ${CYAN}│${RESET}\n"
printf "${CYAN}│${RESET}   ${VIOLET}╔═╗╔═╗╦  ╔╦╗╦ ╦╔═╗╔╗╔${RESET}  ${GOLD}GOLDHEN MANAGER AJ${RESET}   ${CYAN}│${RESET}\n"
printf "${CYAN}│${RESET}   ${VIOLET}║ ╦║ ║║   ║ ║ ║╠═╣║║║${RESET}  ${WHITE}PS4 · TERMUX · v4.0${RESET}  ${CYAN}│${RESET}\n"
printf "${CYAN}│${RESET}   ${VIOLET}╚═╝╚═╝╩═╝ ╩ ╚═╝╩ ╩╝╚╝${RESET}  ${MUTED}AJ · SeBaS${RESET}           ${CYAN}│${RESET}\n"
printf "${CYAN}│${RESET}                                                      ${CYAN}│${RESET}\n"
printf "${CYAN}╰──────────────────────────────────────────────────────╯${RESET}\n\n"

printf "  ${WHITE}Abrir el panel local de PS4${RESET}\n"
printf "  ${MUTED}Enter abre ahora · cualquier otra tecla cancela${RESET}\n\n"

launch=0
for ((remaining=15; remaining>0; remaining--)); do
    elapsed=$((15 - remaining))
    filled=$((elapsed * 20 / 15))
    bar=''
    for ((i=0; i<20; i++)); do
        if ((i < filled)); then bar+="${GOLD}━${RESET}"; else bar+="${MUTED}─${RESET}"; fi
    done
    printf "\r  ${CYAN}INICIANDO EN${RESET} ${WHITE}%02d${RESET} ${MUTED}seg${RESET}  ${bar}  " "$remaining"
    key=''
    if IFS= read -r -s -n 1 -t 1 key; then
        if [[ -z "$key" ]]; then launch=1; else launch=0; fi
        break
    fi
done

if ((remaining == 0)); then launch=1; fi
printf "\n\n"
if ((launch)); then
    printf "  ${GREEN}●${RESET} Abriendo GoldHen Manager…\n\n"
    bash "$HOME/GoldHenManagerAJ/start-goldhen.sh"
else
    clear
    printf "${MUTED}Inicio automático cancelado. Termux está listo.${RESET}\n\n"
fi
