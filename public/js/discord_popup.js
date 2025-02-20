document.addEventListener('DOMContentLoaded', function () {
    // If the modal doesn't exist or we’re skipping it, bail out.
    const modal = document.getElementById('discord-modal');
    if (!modal) return;

    // 1) Check if user is supposed to see the popup at all (server check).
    //    The Blade controller can pass a variable like `shouldShowDiscordPopup`.
    //    For example:
    //    if (!window.shouldShowDiscordPopup) return;
    if (!window.shouldShowDiscordPopup) return;

    // 2) Check for the cookie. If set, do NOT show the popup.
    if (getCookie('dismissedDiscordPopup') === 'true') return;

    // 3) Otherwise, display the modal.
    modal.style.display = 'flex';

    // When user clicks "No, Thanks", hide & set cookie for 7 days.
    document.getElementById('dismiss-discord').addEventListener('click', function () {
        setCookie('dismissedDiscordPopup', 'true', 7);
        modal.style.display = 'none';
    });

    // When user clicks "Yes, Join", open Discord invite in new tab.
    document.getElementById('join-discord').addEventListener('click', function () {
        window.open('https://discord.gg/yourInviteCode', '_blank');
    });
});

// Simple helpers for setting/getting cookies
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