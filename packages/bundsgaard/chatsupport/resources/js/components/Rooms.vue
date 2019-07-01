<template>
    <div>
        <div class="card mb-2" v-for="(room, index) in rooms" :key="index + '-room'">
            <div class="card-body">
                <h5 class="card-title">{{ room.name }}</h5>
                <button class="btn btn-primary" @click="select(room)">Start chat</button>
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
