document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('discord-modal');
    if (modal) {
        modal.style.display = 'block';
    }

    document.getElementById('dismiss-discord').addEventListener('click', function () {
        modal.style.display = 'none';
    });

    document.getElementById('join-discord').addEventListener('click', function () {
        window.location.href = 'https://discord.gg/yourinvitecode';
    });
});
