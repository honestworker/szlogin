<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Notification Detail</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/notification">Notification</router-link></li>
                    <li class="breadcrumb-item active">Notification Detail</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Notification Detail</h4>
                        <notification-comment :id="id"></notification-comment>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NotificationComment from './comment';
    import helper from '../../services/helper'

    export default {
        components : { NotificationComment },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>