window.OneSignal = window.OneSignal || [];
const OneSignal = window.OneSignal;

OneSignal.push(function() {
    OneSignal.init({
        appId: oneSignalAppId, // This variable is set in admin_header.php
        safari_web_id: "web.onesignal.auto.123456-789012-345678-901234", // Example, can be configured in OneSignal dashboard
        notifyButton: {
            enable: true,
        },
        allowLocalhostAsSecureOrigin: true,
    });
});

OneSignal.push(function() {
    // If we're on https, ask for notification permissions
    if (window.location.protocol === "https:") {
        // Occurs when the user's subscription changes to a new value.
        OneSignal.on('subscriptionChange', function(isSubscribed) {
            console.log("The user's subscription state is now:", isSubscribed);
            OneSignal.getUserId(function(userId) {
                console.log("OneSignal User ID:", userId);
                // Send the Player ID to your server
                if (userId) {
                    savePlayerIdToServer(userId);
                }
            });
        });
    }

    // Get the current user's ID
    OneSignal.getUserId(function(userId) {
        console.log("OneSignal User ID:", userId);
        if (userId) {
           savePlayerIdToServer(userId);
        }
    });
});

function savePlayerIdToServer(playerId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../ajax_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            console.log("Server response:", this.responseText);
        }
    }
    xhr.send("action=save_onesignal_player_id&player_id=" + playerId);
}
