{{-- Place this in resources/views/partials/discord_popup.blade.php --}}
<style>
    /* Fullscreen backdrop with blur and semi-transparent overlay */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background-color: rgba(0, 0, 0, 0.3); 
        backdrop-filter: blur(6px);
        
        /* Center the modal box */
        display: none;
        align-items: center;
        justify-content: center;
    }

    /* Smaller, dark modal box */
    .modal-content {
        background-color: #222325;
        color: #fff;
        border-radius: 0.5rem;
        padding: 1.5rem;
        max-width: 500px;
        width: 90%;
    }

    .modal-content h2 {
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .modal-content p {
        margin-bottom: 1rem;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    /* Base button styles */
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-weight: 500;
        border: none;
    }

    /* Red “No, Thanks” button */
    .btn-red {
        background-color: #dc2626;
    }
    .btn-red:hover {
        background-color: #b91c1c;
    }

    /* Green “Yes, Join” button */
    .btn-green {
        background-color: #16a34a;
    }
    .btn-green:hover {
        background-color: #15803d;
    }
</style>

<div id="discord-modal" class="modal-backdrop">
    <div class="modal-content">
        <h2>Join Our Discord!</h2>
        <p>
            Would you like to join our Discord server for updates and community support?
        </p>
        <div class="modal-actions">
            <button id="dismiss-discord" class="btn btn-red">No, Thanks</button>
            <button id="join-discord" class="btn btn-green">Yes, Join</button>
        </div>
    </div>
</div>