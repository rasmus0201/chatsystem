@extends('chatsupport::layout')

@section('content')
    @verbatim
        <div class="row">
            <div class="col-12">
                <div class="overflow-auto border border-primary rounded-sm" style="height: 400px;" ref="messagesContainer">
                    <div class="messages">
                        <p class="p-2 mb-0 message" :class="{ 'client': message.sender !== name }" v-for="(message, index) in messages" :key="index">
                            {{ message.sender }}: {{ message.message }}<br>
                            <small>{{ message.time }}</small>
                        </p>

                        <p class="p-2 mb-0 d-flex align-items-center message client" v-for="(client, index) in typingClients" :key="index">
                            <span class="mr-2">{{ client.name }}:</span>

                            <span class="lds-ellipsis">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-9">
                        <input type="text" class="form-control" v-model="message" placeholder="Besked" v-on:keyup.enter="sendMessage" v-on:keyup="typing($event)">
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-block btn-primary" v-on:click="sendMessage" :disabled="!clients.length">Send</button>
                    </div>
                </div>
            </div>
        </div>
    @endverbatim
@endsection

@section('script')
<script type="text/javascript">
    const app = new Vue({
        data() {
            return {
                message: '',

                clients: [],
                messages: [],
                connection: null,

                name: 'Rasmus',
                identifier: this.getCookie('PHPSESSID'),

                typingTimeouts: []
            }
        },

        computed: {
            typingClients () {
                var typing = [];

                for (client of this.clients) {
                    if (client.typing) {
                        typing.push(client);
                    }
                }

                return typing;
            }
        },

        created() {
            this.openSocket();
        },

        methods: {
            openSocket() {
                var url = 'ws://' + window.location.hostname + '/websocket';

                if (window.location.hostname.match(/\.test/)) {
                    var url = 'ws://' + window.location.hostname + ':9000';
                }

                this.connection = new WebSocket(url);

                this.connection.onopen = this.onOpen;
                this.connection.onmessage = this.onMessage;

                this.connection.onclose = function(e) { console.log(e, 'CLOSE'); };
                this.connection.onerror = function(e) { console.log(e, 'ERROR'); };
            },

            onOpen(e) {
                this.send({
                    type: 'session:connect',
                    data: {
                        language: navigator.language,
                        name: this.name,
                        identifier: this.identifier
                    }
                });
            },

            onMessage(e) {
                var {type, data} = JSON.parse(e.data);

                switch (type) {
                    case 'message':
                        if (!this.assignedTo(data.from)) {
                            return;
                        }

                        this.messages.push(data);
                        this.clearTyper(data.from);
                        this.scroll();

                        break;
                    case 'typing':
                        if (!this.assignedTo(data.from)) {
                            return;
                        }

                        this.clearTyper(data.from);
                        this.setTyper(data.from);
                        this.scroll();

                        break;
                    case 'assign':
                        if (this.assignedTo(data.assignee.identifier)) {
                            return;
                        }

                        this.clients.push(data.assignee);
                        this.messages.push({
                            message: 'Du bliver nu betjent af ' + data.assignee.name,
                            sender: 'System',
                            time: data.time
                        });
                        this.scroll();

                        break;
                    case 'unassign':
                        if (!this.assignedTo(data.assignee.identifier)) {
                            return;
                        }

                        this.unassign(data.assignee.identifier);
                        this.messages.push({
                            message: data.assignee.name + ' har forladt chatten.',
                            sender: 'System',
                            time: data.time
                        });
                        this.scroll();

                        break;
                }
            },

            send(data) {
                this.connection.send(JSON.stringify(data));
            },

            sendMessage() {
                var message = this.message.trim();

                if (message === '' || !this.clients.length) {
                    return;
                }

                for (client of this.clients) {
                    this.send({
                        type: 'message',
                        data: {
                            message: message,
                            to: client.identifier
                        }
                    });
                }

                this.messages.push({
                    from: this.identifier,
                    message: message,
                    sender: this.name,
                    time: this.getTime()
                });
                this.message = '';

                this.scroll();
            },

            typing: _.throttle(function(e) {
                if (e.metaKey || e.ctrlKey || e.altKey || e.key === 'Enter') {
                    return;
                }

                if (!this.clients.length) {
                    return;
                }

                for (client of this.clients) {
                    this.send({
                        type: 'typing',
                        data: {
                            to: client.identifier
                        }
                    });
                }
            }, 350),

            getClientIndex(identifier) {
                for (var i = 0; i < this.clients.length; i++) {
                    if (identifier === this.clients[i].identifier) {
                        return i;
                    }
                }

                return undefined;
            },

            assignedTo(identifier) {
                return typeof this.getClientIndex(identifier) !== 'undefined';
            },

            unassign(identifier) {
                var index = this.getClientIndex(identifier);

                this.$delete(this.clients, index);
            },

            setTyper(identifier) {
                this.typingTimeouts[identifier] = setTimeout(function() {
                    this.clearTyper(identifier);
                }.bind(this), 1000);

                var index = this.getClientIndex(identifier);
                this.clients[index].typing = true;
            },

            clearTyper(identifier) {
                clearTimeout(this.typingTimeouts[identifier]);

                var index = this.getClientIndex(identifier);
                this.clients[index].typing = false;
            },

            scroll() {
                this.$nextTick(() => {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                });
            },

            getCookie(name) {
                var pair = document.cookie.split(';').find(x => x.startsWith(name + '='));

                if (!pair) {
                    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);;
                }

                return pair.split('=')[1];
            },

            getTime() {
                var now = new Date();

                var time = '';

                var hours = now.getHours();
                var min = now.getMinutes();
                var sec = now.getSeconds();

                if (hours < 10) {
                    time += '0';
                }
                time += hours + ':';

                if (min < 10) {
                    time += '0';
                }
                time += min + ':';

                if (sec < 10) {
                    time += '0';
                }
                time += sec;

                return time;
            }
        },
    }).$mount('#app');
</script>
@endsection
