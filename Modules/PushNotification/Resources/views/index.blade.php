<!-- The core Firebase JS SDK is always required and must be listed first -->

<script src={{ asset('frontend/plugins/firebase/firebase.js') }}></script>
<script>
    var setting = {!! $setting !!};
    if (authId_global) {
        if (setting) {
            if (setting.push_notification_status) {

                var firebaseConfig = {
                    apiKey: setting ? setting.api_key : '',
                    authDomain: setting ? setting.auth_domain : '',
                    projectId: setting ? setting.project_id : '',
                    storageBucket: setting ? setting.storage_bucket : '',
                    messagingSenderId: setting ? setting.messaging_sender_id : '',
                    appId: setting ? setting.app_id : '',
                    measurementId: setting ? setting.measurement_id : ''
                };
                firebase.initializeApp(firebaseConfig);
                const messaging = firebase.messaging();

                function startFCM() {
                    messaging
                        .requestPermission()
                        .then(function() {
                            return messaging.getToken();
                        })
                        .then(function(response) {
                            $.ajax({
                                url: '{{ route('store.token') }}',
                                type: 'POST',
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    token: response
                                },
                                dataType: 'JSON',
                                success: function(response) {
                                    // alert('Token stored.');
                                },
                                error: function(error) {
                                    // alert(error);
                                },
                            });
                        }).catch(function(error) {
                            // alert(error);
                        });
                }

                startFCM();

                messaging.onMessage(function(payload) {
                    const title = payload.notification.title;
                    const options = {
                        body: payload.notification.body,
                        icon: setting ? setting.favicon_image_url : payload.notification.icon,
                    };
                    new Notification(title, options);
                });
            }
        }
    }
</script>
