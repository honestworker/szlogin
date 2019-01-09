import VueRouter from 'vue-router'
import helper from './services/helper'

let routes = [
    {
        path: '/',
        component: require('./layouts/default-page'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '/',
                component: require('./views/dashboard/home')
            },
            {
                path: '/dashboard',
                component: require('./views/dashboard/home')
            },
            {
                path: '/setting',
                component: require('./views/setting/index')
            },
            {
                path: '/group',
                component: require('./views/group/index')
            },
            {
                path: '/profile',
                component: require('./views/admin/profile')
            },
            {
                path: '/group/:id/edit',
                component: require('./views/group/edit')
            },
            {
                path: '/user',
                component: require('./views/user/index')
            },
            {
                path: '/user/:id/view',
                component: require('./views/user/view')
            },
            {
                path: '/country',
                component: require('./views/country/index')
            },
            {
                path: '/notification',
                component: require('./views/notification/index')
            },
            {
                path: '/notification/:id',
                component: require('./views/notification/edit')
            },
            {
                path: '/noti_type',
                component: require('./views/notification/type')
            },
            {
                path: '/sys_noti',
                component: require('./views/sys_noti/index')
            },
            {
                path: '/sys_noti/:id',
                component: require('./views/sys_noti/edit')
            },
            {
                path: '/advertisement',
                component: require('./views/advertisement/index')
            },
            {
                path: '/advertisement/:id',
                component: require('./views/advertisement/edit')
            },
            {
                path: '/admin',
                component: require('./views/admin/index')
            },
            {
                path: '/admin/:id/view',
                component: require('./views/admin/view')
            },
        ]
    },
    {
        path: '/',
        component: require('./layouts/guest-page'),
        meta: { requiresGuest: true },
        children: [
            {
                path: '/login',
                component: require('./views/auth/login')
            },
            {
                path: '/forgot_password',
                component: require('./views/auth/forgot_password')
            },
            {
                path: '/register',
                component: require('./views/auth/register')
            },
            {
                path: '/success_register',
                component: require('./views/auth/success_register')
            },
            {
                path: '/signup',
                component: require('./views/auth/signup')
            },
            {
                path: '/msignup',
                component: require('./views/auth/msignup')
            },
            {
                path: '/success_signup',
                component: require('./views/auth/success_signup')
            },
            {
                path: '/auth/:token/activate',
                component: require('./views/auth/activate')
            },
            {
                path: '/password/reset/:token',
                component: require('./views/auth/reset')
            },
            {
                path: '/auth/social',
                component: require('./views/auth/social-auth')
            },
        ]
    },
    {
        path: '*',
        component : require('./layouts/error-page'),
        children: [
            {
                path: '*',
                component: require('./views/errors/page-not-found')
            }
        ]
    }
];

const router = new VueRouter({
	routes,
    linkActiveClass: 'active',
    mode: 'history'
});

router.beforeEach((to, from, next) => {

    if (to.matched.some(m => m.meta.requiresAuth)){
        return helper.check().then(response => {
            if(!response) {
                return next({ path : '/login'})
            }
            
            return next()
        })
    }

    if (to.matched.some(m => m.meta.requiresGuest)){
        return helper.check().then(response => {
            if(response){
                return next({ path : '/'})
            }
            
            return next()
        })
    }

    return next()
});

export default router;
