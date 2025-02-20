document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.shouldShowDiscordPopup !== 'undefined' && window.shouldShowDiscordPopup === false) {
        return;
    }
    
    const modal = document.getElementById('discord-modal');
    if (!modal) return; 

    if (getCookie('dismissedDiscordPopup') === 'true') {
        return;
    }

    modal.style.display = 'flex';

    const dismissBtn = document.getElementById('dismiss-discord');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            setCookie('dismissedDiscordPopup', 'true', 2);
            modal.style.display = 'none';
        });
    }

    const joinBtn = document.getElementById('join-discord');
    if (joinBtn) {
        joinBtn.addEventListener('click', function () {
            window.open('https://discord.gg/ENhNqXfDad', '_blank');
        });
    }
});

function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = 'expires=' + d.toUTCString();
    document.cookie = name + '=' + value + ';' + expires + ';path=/';
}

function getCookie(name) {
    const cname = name + '=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(cname) === 0) {
            return c.substring(cname.length, c.length);
        }
    }
    return '';
}