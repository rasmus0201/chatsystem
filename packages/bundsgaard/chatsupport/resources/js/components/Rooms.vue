<template>
    <div>
        <div class="card mb-2" v-for="(room, index) in rooms" :key="index + '-room'">
            <div class="card-body">
                <p class="card-title">{{ room.name }}</p>
                <button class="btn btn-primary" @click="select(room)">Start chat</button>
            </div>
        </div>
        <div class="card mb-2" v-if="!rooms.length">
            <div class="card-body">
                <p class="card-title">Ingen tilgængelige chats</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['rooms'],

        methods: {
            select(room) {
                this.$emit('select', room);
            }
        }
    }
</script>
