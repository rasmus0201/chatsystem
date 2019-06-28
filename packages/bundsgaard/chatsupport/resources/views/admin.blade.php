@extends('chatsupport::layout')

@section('content')
    @verbatim
        <div class="row">
            <div class="col-4">
                <ul class="list-group" style="min-height: 400px;">
                    <li class="list-group-item d-flex justify-content-between align-items-center" :class="{ 'active': activeClient(client.identifier) }" v-for="(client, index) in clients" :key="index">
                        <div class="d-flex align-items-center" v-on:click="conversation(client)">
                            <span v-if="unseenMessages(client.identifier)" class="indicator bg-primary mr-1"></span>
                            <span>
                                {{ client.name }}
                            </span>
                        </div>

                        <div>
                            <button type="button" class="btn btn-primary btn-sm" v-on:click="conversation(client)" v-text="assignedTo(client.identifier) ? 'Vælg' : 'Forbind'"></button>
                            <button type="button" class="btn btn-danger btn-sm" v-on:click="ban(client)">Ban</button>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center" v-if="clients.length === 0">
                        Der er ingen brugere.
                    </li>
                </ul>
            </div>
            <div class="col-8">
                <div class="overflow-auto border border-primary rounded-sm" style="height: 400px;" ref="messagesContainer">
                    <p class="p-2 mb-0 bg-light border-bottom" v-if="currentClient.identifier" style="position: sticky;">
                        {{ currentClient.name }} - ({{ currentClient.language }}) <small>(Session: {{ currentClient.identifier }})</small>
                        <button class="btn btn-sm btn-warning" v-on:click="unassign(currentClient)">Afslut</button>
                    </p>
                    <div class="messages">
                        <p class="p-2 mb-0 message" :class="{ 'client': message.sender !== name }" v-for="(message, index) in currentMessages" :key="index">
                            {{ message.sender }}: {{ message.message }}<br>
                            <small>{{ message.time }}</small>
                        </p>

                        <p class="p-2 mb-0 d-flex align-items-center message client" v-if="currentClient.typing">
                            <span class="mr-2">{{ currentClient.name }}:</span>

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
                        <button type="button" class="btn btn-block btn-primary" v-on:click="sendMessage" :disabled="!currentClient.identifier">Send</button>
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
                assignedClients: [],
                currentClient: {},
                currentMessages: [],
                connection: null,

                name: 'Supporter',
                identifier: this.getCookie('PHPSESSID'),

                typingTimeout: null
            }
        },

        created() {
            this.openSocket();
        },

        methods: {
            openSocket() {
                var host = window.location.hostname;
                var url = 'ws://' + host + '/websocket';

                if (host.match(/\.(test|localhost|dev)/)) {
                    var url = 'ws://' + host + ':9000';
                }

                this.connection = new WebSocket(url);

                this.connection.onopen = this.onOpen;
                this.connection.onmessage = this.onMessage;

                // Make some kind of error handler when connection gets closed
                // or an error occurred.
                this.connection.onclose = function(e) { console.log(e, 'CLOSE'); };
                this.connection.onerror = function(e) { console.log(e, 'ERROR'); };
            },

            onOpen(e) {
                this.send({
                    type: 'session:connect',
                    data: {
                        language: navigator.language,
                        name: this.name,
                        identifier: this.identifier,
                        credentials: {
                            username: 'test',
                            password: 'test'
                        }
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

                        if (!this.activeClient(data.from)) {
                            this.$set(this.assignedClients[data.from], 'unseen', true);

                            this.$forceUpdate();
                        }

                        var messages = this.assignedClients[data.from].messages;
                        messages.push(data);

                        this.$set(this.assignedClients[data.from], 'messages', messages);

                        clearTimeout(this.typingTimeout);
                        this.currentClient.typing = false;

                        this.$nextTick(() => {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        });

                        break;
                    case 'typing':
                        if (!this.assignedTo(data.from)) {
                            return;
                        }

                        if (this.currentClient.identifier !== data.from) {
                            return;
                        }

                        clearTimeout(this.typingTimeout);
                        this.currentClient.typing = true;

                        this.typingTimeout = setTimeout(function() {
                            this.currentClient.typing = false;
                        }.bind(this), 1000);

                        this.$nextTick(() => {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        });
                        break;
                    case 'user:list':
                        const newClients = data.users.map((a) => {
                            return a.identifier;
                        });

                        // Active client has disconnected
                        if (this.currentClient.identifier && !newClients.includes(this.currentClient.identifier)) {
                            this.currentMessages.push({
                                message: this.currentClient.name + ' har lukket chatten.',
                                sender: 'System',
                                time: this.getTime()
                            });
                            this.currentClient = {};

                            this.$nextTick(() => {
                                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                            });
                        }

                        // Maybe check if some assigned client has disconnected.

                        this.clients = data.users;

                        break;
                }
            },

            send(data) {
                this.connection.send(JSON.stringify(data));
            },

            sendMessage() {
                var message = this.message.trim();

                if (message === '' || !this.currentClient.identifier) {
                    return;
                }

                this.send({
                    type: 'message',
                    data: {
                        message: message,
                        to: this.currentClient.identifier
                    }
                });

                var messages = this.assignedClients[this.currentClient.identifier].messages;
                var msg = {
                    from: this.identifier,
                    message: message,
                    sender: this.name,
                    time: this.getTime()
                };
                messages.push(msg);
                this.$set(this.assignedClients[this.currentClient.identifier], 'messages', messages);

                this.message = '';

                this.$nextTick(() => {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                });
            },

            typing: _.throttle(function(e) {
                if (e.metaKey || e.ctrlKey || e.altKey || e.key === 'Enter') {
                    return;
                }

                if (!this.currentClient.identifier) {
                    return;
                }

                this.send({
                    type: 'typing',
                    data: {
                        to: this.currentClient.identifier
                    }
                });
            }, 350),

            conversation(client) {
                if (!this.assignedTo(client.identifier)) {
                    this.send({
                        type: 'assign',
                        data: {
                            assignee: this.identifier,
                            to: client.identifier
                        }
                    });

                    client.messages = [];
                    client.unseen = false;

                    this.assignedClients[client.identifier] = client;
                }

                this.assignedClients[client.identifier].unseen = false;
                this.currentClient = this.assignedClients[client.identifier];
                this.currentMessages = this.currentClient.messages;
            },

            unassign(client) {
                this.send({
                    type: 'unassign',
                    data: {
                        assignee: this.identifier,
                        to: client.identifier
                    }
                });

                this.currentClient = {};
                this.currentMessages = [];
                delete this.assignedClients[client.identifier];
            },

            assignedTo(identifier) {
                return typeof this.assignedClients[identifier] !== 'undefined';
            },

            activeClient(identifier) {
                return identifier === this.currentClient.identifier;
            },

            unseenMessages(identifier) {
                if (typeof this.assignedClients[identifier] === 'undefined') {
                    return false;
                }

                return this.assignedClients[identifier].unseen === true;
            },

            getCookie(name) {
                var pair = document.cookie.split(';').find(x => x.startsWith(name + '='));

                if (!pair) {
                    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
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
