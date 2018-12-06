<template>
	<aside class="left-sidebar">
        <div class="scroll-sidebar">
            <div class="user-profile">
                <div class="profile-img"> <img :src="getAvatar" alt="user" /> </div>
                <div class="profile-text"> <a href="#" class="dropdown-toggle link u-dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">{{getAuthUserContactPserson()}}<span class="caret"></span></a>
                    <div class="dropdown-menu">
                        <router-link to="/profile" class="dropdown-item"><i class="fa fa-user"></i> Profile</router-link>
                        <!-- <div class="dropdown-divider"></div> <router-link to="/configuration" class="dropdown-item"><i class="fa fa-cogs"></i> Configuration</router-link> -->
                        <div class="dropdown-divider"></div> <a href="#" class="dropdown-item" @click.prevent="logout"><i class="fa fa-power-off"></i> Logout</a>
                    </div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul id="sidebarnav" class="collapsible">
                    <li>
                        <router-link to="/dashboard" exact><i class="fa fa-dashboard"></i> <span class="hide-menu">Dashboard</span></router-link>
                    </li>
                    <li>
                        <router-link to="/admin" exact><i class="	fa fa-user-secret"></i> <span class="hide-menu">Administrators</span></router-link>
                    </li>
                    <!-- <li>
                        <router-link to="/setting" exact><i class="fa fa-cog"></i> <span class="hide-menu">Settings</span></router-link>
                    </li> -->
                    <li>
                        <router-link to="/group" exact><i class="fa fa-group"></i> <span class="hide-menu">Groups</span></router-link>
                    </li>
                    <li>
                        <router-link to="/user" exact><i class="fa fa-user"></i> <span class="hide-menu">All Users</span></router-link>
                    </li>
                    <!-- <li>
                        <router-link to="/notification" exact><i class="fa fa-bell"></i> <span class="hide-menu">Notifications</span></router-link>
                    </li> -->
                    <!-- <li>
                        <router-link to="/noti_type" exact><i class="fa fa-gavel"></i> <span class="hide-menu">Notification Type</span></router-link>
                    </li> -->
                    <li>
                        <router-link to="/advertisement" exact><i class="fa fa-paw"></i> <span class="hide-menu">Advertisements</span></router-link>
                    </li>
                    <li>
                        <router-link to="/country" exact><i class="fa fa-globe"></i> <span class="hide-menu">Country</span></router-link>
                    </li>
                    <li>
                        <a href="#" @click.prevent="logout"><i class="fa fa-power-off"></i> <span class="hide-menu">Logout</span></a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="sidebar-footer">
            <router-link to="/configuration" class="link" data-toggle="tooltip" title="Configuration"><i class="fa fa-cogs"></i></router-link>
            <router-link to="/profile" class="link" data-toggle="tooltip" title="Profile"><i class="fa fa-user"></i></router-link>
            <a href="#" class="link" data-toggle="tooltip" title="Logout" @click.prevent="logout"><i class="fa fa-power-off"></i></a>
        </div>
    </aside>
</template>

<script>

    import helper from '../services/helper'

    export default {
        mounted() {
        },
        methods : {
            logout(){
                helper.logout().then(() => {
                    this.$store.dispatch('resetAuthUserDetail');
                    this.$router.replace('/login')
                })
            },
            getAuthUserContactPserson(){
                return this.$store.getters.getAuthUserContactPserson;
            },
            getAuthUser(name){
                return this.$store.getters.getAuthUser(name);
            }
        },
        computed: {
            getAvatar(){
                if (this.getAuthUser('avatar')) {
                    return '/images/users/' + this.getAuthUser('avatar');
                } else {
                    return '/images/common/no-user.png';
                }
            }
        }
    }
</script>
