<!-- Discord Popup Modal -->
<div
    id="discord-modal"
    class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30 backdrop-blur-sm"
    style="display: none;"
>
    <!-- Modal Container -->
    <div class="bg-neutral-900 text-white rounded-lg shadow-lg p-6 w-full max-w-lg mx-4">
        <h2 class="text-2xl font-semibold mb-4">Join Our Discord!</h2>
        <p class="mb-6">
            Would you like to join our Discord server for updates and community support?
        </p>
        <div class="flex justify-end space-x-3">
            <button
                id="dismiss-discord"
                class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded"
            >
                No, Thanks
            </button>
            <button
                id="join-discord"
                class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded"
            >
                Yes, Join
            </button>
        </div>
    </div>
</div>