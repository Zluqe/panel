document.addEventListener('DOMContentLoaded', function () {
    // Grab the modal element.
    var modal = document.getElementById('discord-modal');
    if (!modal) return;

    // Display the modal (flex layout for centering).
    modal.style.display = 'flex';

    // Handle dismiss button click.
    var dismissBtn = document.getElementById('dismiss-discord');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    // Handle join button click.
    var joinBtn = document.getElementById('join-discord');
    if (joinBtn) {
        joinBtn.addEventListener('click', function () {
            window.open('https://discord.gg/ENhNqXfDad', '_blank');
        });
    }
});