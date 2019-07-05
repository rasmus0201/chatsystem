<template>
    <div>
        <rooms :rooms="rooms" @select="setRoom($event)" v-if="!user.room.id"></rooms>
        <chat-window v-else
            ref="chat"
            :user="user"
            :conversation="conversation"
            @conversation:leave="leave($event)"
            @message:send="sendMessage($event)"
            @message:type="send($event)">

        </chat-window>
    </div>
</template>

<script>
    import TimeService from '../services/timeService';
    import commonChat from '../services/commonChat';

    export default {
        mixins: [commonChat],
        data() {
            return {
                connection: null,
                user: {
                    room: {},
                    name: 'Rasmus',
                    session_id: window.Chatsupport.session
                },
                conversation: {},
                typingTimeouts: [],
            }
        },

        props: ['rooms'],

        created() {
            this.reset();

            this.openSocket();
        },

        methods: {
            onMessage(e) {
                var {type, data} = JSON.parse(e.data);

                switch (type) {
                    case 'message':
                        if (!this.conversation.clients.length) {
                            return;
                        }

                        this.messages.push(data);
                        this.clearTyper(data.from);
                        this.scroll();

                        break;
                    case 'room':
                        this.room = { id: data.room_id };
                        break;
                    case 'conversation':
                        this.conversation = Object.assign({}, this.conversation, data.conversation);
                        this.user.room = { id: data.room_id };
                        this.scroll();

                        break;
                    case 'typing':
                        if (!this.conversation.clients.length) {
                            return;
                        }

                        this.clearTyper(data.from);
                        this.setTyper(data.from);
                        this.scroll();

                        break;
                    case 'assign':
                        this.conversation.clients.push(data.assignee);
                        this.conversation.messages.push(data.message);
                        this.scroll();

                        break;
                    case 'unassign':
                        if (!this.conversation.clients.length) {
                            return;
                        }

                        this.unassign(data.assignee.session_id);
                        this.scroll();

                        break;
                }
            },

            leave(event) {
                this.send(event);
                this.reset();

                // Force update views
                this.$refs['chat'].$forceUpdate();
                this.$forceUpdate();
            },

            getClientIndex(sessionId) {
                for (var i = 0; i < this.conversation.clients.length; i++) {
                    const client = this.conversation.clients[i];

                    if (client.session_id === sessionId) {
                        return i;
                    }
                }

                return undefined;
            },

            unassign(sessionId) {
                var index = this.getClientIndex(sessionId);

                this.$delete(this.conversation.clients, index);
            },

            reset() {
                this.conversation = Object.assign({}, {}, {}); // Reset the object this way Vue is reactive to the change.
                this.$set(this.conversation, 'messages', []);
                this.$set(this.conversation, 'clients', []);
                this.$set(this.conversation, 'typingClients', []);

                this.typingTimeouts = [];
                this.user.room = {};
            }
        },
    }
</script>
