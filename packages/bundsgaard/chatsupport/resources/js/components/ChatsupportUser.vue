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
                        if (!this.assignedTo(data.from)) {
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
                        this.conversation = Object.assign(this.conversation, data.conversation);
                        this.user.room = { id: data.room_id };

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
                        if (this.assignedTo(data.assignee.session_id)) {
                            return;
                        }

                        this.clients.push(data.assignee);
                        this.messages.push(data.message);
                        this.scroll();

                        break;
                    case 'unassign':
                        if (!this.assignedTo(data.assignee.session_id)) {
                            return;
                        }

                        this.unassign(data.assignee.session_id);
                        this.messages.push({
                            message: data.assignee.name + ' har forladt chatten.',
                            sender: 'System',
                            time: data.time
                        });
                        this.scroll();

                        break;
                }
            },

            getClientIndex(session_id) {
                for (var i = 0; i < this.clients.length; i++) {
                    if (session_id === this.clients[i].session_id) {
                        return i;
                    }
                }

                return undefined;
            },

            assignedTo(session_id) {
                return (session_id === 'SYSTEM') || (typeof this.getClientIndex(session_id) !== 'undefined');
            },

            leave(event) {
                // TODO Make this (closes the conversation)
                this.send(event);
                this.reset();
            },

            reset() {
                this.conversation = {};
                this.$set(this.conversation, 'messages', []);
                this.$set(this.conversation, 'clients', []);
                this.$set(this.conversation, 'typingClients', []);

                this.typingTimeouts = [];
                this.user.room = {};
            }
        },
    }
</script>
