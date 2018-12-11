<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0" v-if="id != 0">Edit System Notification</h3>
                <h3 class="text-themecolor m-b-0 m-t-0" v-else>Create System Notification</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/sys_noti">System Notification</router-link></li>
                    <li class="breadcrumb-item active" v-if="id != 0">Edit System Notification</li>
                    <li class="breadcrumb-item active" v-else>Create System Notification</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" v-if="id != 0">Edit System Notification</h4>
                        <h4 class="card-title" v-else>Create System Notification</h4>
                        <sys-notification-form :id="id"></sys-notification-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SysNotificationForm from './form';
    import helper from '../../services/helper'

    export default {
        components : { SysNotificationForm },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>