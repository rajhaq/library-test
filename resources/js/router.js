import Vue from 'vue'
import Router from 'vue-router'


import user from './components/dashboard/user/Home.vue'
import userlist from './components/dashboard/user/userlist.vue'
import profile from './components/dashboard/user/profile.vue'


import bookHome from './components/dashboard/pizza/Home.vue'
import bookList from './components/dashboard/book/bookList.vue'
import myBook from './components/dashboard/book/myBook.vue'
import assignedBook from './components/dashboard/book/assignedBook.vue'

let onlyAdmin = [1]
let onlyLibrary = [1,2]
let onlyReader = [3]
let allUser = [1,2,3, 4]
let onlyGuest = ['customer']

Vue.use(Router)
export default new Router({
    mode: 'history',
    routes: [


        {
            path: '/home',
            name: 'book',
            component: bookHome,
            meta: {
                icon: 'bookmark',
                title: "Book",
                type: allUser,
                status: true,
            },
            children: [
                {
                    path: 'list',
                    name: 'bookList',
                    component: bookList,
                    meta: {
                        icon: 'bookmarks',
                        title: "Book List",
                        type: allUser,
                        status: true,
        
                    }
                },
                {
                    path: 'mylist',
                    name: 'mybookList',
                    component: myBook,
                    meta: {
                        icon: 'bookmarks',
                        title: "My Books",
                        type: onlyReader,
                        status: true,
        
                    }
                },
                {
                    path: 'assigned',
                    name: 'assignedBook',
                    component: assignedBook,
                    meta: {
                        icon: 'bookmarks',
                        title: "Assigned Books",
                        type: onlyLibrary,
                        status: true,
        
                    }
                },
            ]
        },
        {
            path: '/user',
            name: 'user',
            component: user,
            meta: {
                icon: 'face',
                title: "User",
                type: allUser,
                status: true,
            },
            children: [
                {
                    path: 'list',
                    name: 'userlist',
                    component: userlist,
                    meta: {
                        icon: 'dashboard',
                        title: "User List",
                        type: onlyAdmin,
                        status: true,
        
                    }
                },
                {
                    path: 'profile',
                    name: 'profile',
                    component: profile,
                    meta: {
                        icon: 'dashboard',
                        title: "Profile",
                        type: allUser,
                        status: true,
        
                    }
                },
            ]

        },
       




    ]
})