<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">View Group Users</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/group">Group</router-link></li>
                    <li class="breadcrumb-item active">View Group Users</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View Group Users</h4>
                        <group-user :id="id"></group-user>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import GroupUser from './user';
    import helper from '../../services/helper'

    export default {
        components : { GroupUser },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>