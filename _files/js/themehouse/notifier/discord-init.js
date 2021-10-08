!function ($, window, document, _undefined) {
    "use strict";

    XF.NotifierDiscordInit = XF.Element.newHandler({
        init: function () {
            var input = $(this.$target), container = input.closest('form'),
                run = false;

            container.find('button').on('click', function (event) {
                if(!run) {
                    event.preventDefault();

                    var socket = new WebSocket('wss://gateway.discord.gg/?encoding=json&v=6');

                    socket.onmessage = function (msg) {
                        var data = JSON.parse(msg.data), s = data.s;

                        if (data.op == 10) {
                            socket.send(JSON.stringify({
                                op: 2,
                                d: {
                                    token: input.val(),
                                    properties: {
                                        $browser: 'xf/notifier',
                                        $device: 'xf/notifier'
                                    }
                                }
                            }));

                            console.log('Heartbeat sent.');
                            run = true;
                            $(event.target).trigger('click');

                            setInterval(function () {
                                socket.send(JSON.stringify({
                                        op: 1,
                                        d: s
                                    })
                                );
                            }, data.d.heartbeat_interval);
                        }
                    };
                }
            });
        }
    });

    XF.Element.register('discord-init', 'XF.NotifierDiscordInit');
}(jQuery, window, document);