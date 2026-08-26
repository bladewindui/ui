const css_classes_to_pre_compile =
    {
        bg: `bg-primary-100/70 bg-blue-100/70 bg-red-100/70 bg-yellow-100/70 bg-green-100/70 
                bg-purple-100/70 bg-orange-100/70 bg-slate-100/70 bg-gray-100/70 bg-pink-100/70 
                bg-cyan-100/70 bg-violet-100/70 bg-indigo-100/70 bg-fuchsia-100/70 bg-primary-100 
                bg-blue-100 bg-red-100 bg-yellow-100 bg-green-100 bg-purple-100 bg-orange-100 bg-slate-100 
                bg-gray-100 bg-pink-100 bg-cyan-100 bg-violet-100 bg-indigo-100 bg-fuchsia-100
                bg-primary-200/80 bg-blue-200/80 bg-red-200/80 bg-yellow-200/80 bg-green-200/80 bg-purple-200/80
                bg-orange-200/80 bg-slate-200/80 bg-gray-200/80 bg-pink-200/80 bg-cyan-200/80 bg-violet-200/80
                bg-indigo-200/80 bg-fuchsia-200/80
                bg-primary-300 bg-secondary-300 bg-blue-300 bg-red-300 bg-yellow-300 bg-green-300 bg-purple-300
                bg-orange-300 bg-slate-300 bg-gray-300 bg-pink-300 bg-cyan-300 bg-violet-300
                bg-indigo-300 bg-fuchsia-300 bg-primary-600
                bg-primary-500 bg-secondary-500 bg-blue-500 bg-red-500 bg-yellow-500 bg-green-500 bg-purple-500 
                bg-orange-500 bg-slate-500 bg-gray-500 bg-pink-500 bg-cyan-500 bg-violet-500 bg-indigo-500 
                bg-fuchsia-500 bg-black group-hover:bg-primary-500 group-hover:bg-secondary-500 group-hover:bg-blue-500 
                group-hover:bg-red-500 group-hover:bg-yellow-500 group-hover:bg-green-500 group-hover:bg-purple-500 
                group-hover:bg-orange-500 group-hover:bg-slate-500 group-hover:bg-gray-500 group-hover:bg-pink-500 
                group-hover:bg-cyan-500 group-hover:bg-violet-500 group-hover:bg-indigo-500 group-hover:bg-fuchsia-500 
                group-hover:bg-black bg-primary-500! bg-secondary-500! bg-blue-500! bg-red-500! bg-yellow-500! 
                bg-green-500! bg-purple-500! bg-orange-500! bg-slate-500!
                bg-gray-500! bg-pink-500! bg-cyan-500! bg-violet-500! bg-indigo-500! bg-fuchsia-500! bg-black!
                hover:bg-primary-600 hover:bg-secondary-600 hover:bg-blue-600 hover:bg-red-600 hover:bg-yellow-600 
                hover:bg-green-600 hover:bg-purple-600 hover:bg-orange-600 hover:bg-slate-600 hover:bg-gray-600 
                hover:bg-pink-600 hover:bg-cyan-600 hover:bg-violet-600 hover:bg-indigo-600 hover:bg-fuchsia-600
                hover:bg-primary-600! hover:bg-secondary-600! hover:bg-blue-600! hover:bg-red-600! hover:bg-yellow-600! 
                hover:bg-green-600! hover:bg-purple-600! hover:bg-orange-600! hover:bg-slate-600! hover:bg-gray-600! 
                hover:bg-pink-600! hover:bg-cyan-600! hover:bg-violet-600! hover:bg-indigo-600! hover:bg-fuchsia-600!
                dark:bg-primary-600 dark:bg-secondary-600 dark:bg-blue-600 dark:bg-red-600 dark:bg-yellow-600 
                dark:bg-green-600 dark:bg-purple-600 dark:bg-orange-600 dark:bg-slate-600 dark:bg-gray-600 
                dark:bg-pink-600 dark:bg-cyan-600 dark:bg-violet-600 dark:bg-indigo-600 dark:bg-fuchsia-600
                active:bg-primary-600! active:bg-blue-600! active:bg-red-600! active:bg-yellow-600! active:bg-green-600!
                active:bg-purple-600! active:bg-orange-600! active:bg-slate-600! active:bg-gray-600! active:bg-pink-600!
                active:bg-cyan-600! active:bg-violet-600! active:bg-indigo-600! active:bg-fuchsia-600!
                peer-checked:bg-primary-600 peer-checked:bg-blue-600 peer-checked:bg-red-600 peer-checked:bg-yellow-600 
                peer-checked:bg-green-600 peer-checked:bg-purple-600 peer-checked:bg-orange-600 peer-checked:bg-slate-600 
                peer-checked:bg-gray-600 peer-checked:bg-pink-600 peer-checked:bg-cyan-600 peer-checked:bg-violet-600 
                peer-checked:bg-indigo-600 peer-checked:bg-fuchsia-600`,

        text: `text-white text-primary-50 text-blue-50 text-red-50 text-yellow-50 text-green-50 text-purple-50 text-orange-50
                    text-gray-50 text-slate-50 text-pink-50 text-cyan-50 text-violet-50 text-indigo-50 text-fuchsia-50
                    hover:text-primary-50 hover:text-blue-50 hover:text-red-50 hover:text-yellow-50 hover:text-green-50
                    hover:text-purple-50 hover:text-orange-50 hover:text-slate-50 hover:text-pink-50 hover:text-cyan-50
                    hover:text-violet-50 hover:text-indigo-50 hover:text-fuchsia-50 hover:text-gray-50
                    text-primary-600 text-blue-600 text-red-600 text-yellow-600 text-green-600 text-purple-600
                    text-orange-600 text-slate-600 text-pink-600 text-cyan-600 text-violet-600 text-indigo-600 text-fuchsia-600
                    dark:text-primary-100 dark:text-blue-100 dark:text-red-100 dark:text-yellow-100 dark:text-green-100
                    dark:text-purple-100 dark:text-orange-100 dark:text-slate-100 dark:text-pink-100 dark:text-cyan-100
                    dark:text-violet-100 dark:text-indigo-100 dark:text-fuchsia-100
                    dark:text-primary-300! dark:text-blue-300! dark:text-red-300! dark:text-yellow-300! dark:text-green-300!
                    dark:text-purple-300! dark:text-orange-300! dark:text-slate-300! dark:text-pink-300! dark:text-cyan-300!
                    dark:text-violet-300! dark:text-indigo-300! dark:text-fuchsia-300!
                    text-center!`,

        lines: {
            borders: `border-2 border-4 border-8 border-black
                    after:border-primary-100 after:border-blue-100 after:border-red-100 after:border-yellow-100
                    after:border-green-100 after:border-purple-100 after:border-orange-100 after:border-slate-100
                    after:border-gray-100 after:border-pink-100 after:border-cyan-100 after:border-violet-100
                    after:border-indigo-100 after:border-fuchsia-100
                    border-primary-200 border-secondary-200 border-blue-200 border-red-200 border-yellow-200
                    border-green-200 border-purple-200 border-orange-200 border-slate-200 border-gray-200
                    border-pink-200 border-cyan-200 border-violet-200 border-indigo-200 border-fuchsia-200
                    border-primary-400/50 border-secondary-400/50 border-blue-400/50 border-red-400/50 border-yellow-400/50
                    border-green-400/50 border-purple-400/50 border-orange-400/50 border-slate-400/50 border-gray-400/50
                    border-pink-400/50 border-cyan-400/50 border-violet-400/50 border-indigo-400/50 border-fuchsia-400/50
                    border-primary-500 border-secondary-500 border-blue-500 border-red-500 border-yellow-500
                    border-green-500 border-purple-500 border-orange-500 border-slate-500 border-gray-500
                    border-pink-500 border-cyan-500 border-violet-500 border-indigo-500 border-fuchsia-500
                    border-l-primary-500 border-l-secondary-500 border-l-blue-500 border-l-red-500 border-l-yellow-500
                    border-l-green-500 border-l-purple-500 border-l-orange-500 border-l-slate-500 border-l-gray-500
                    border-l-pink-500 border-l-cyan-500 border-l-violet-500 border-l-indigo-500 border-l-fuchsia-500
                    border-primary-500/50 border-secondary-500/50 border-blue-500/50 border-red-500/50
                    border-yellow-500/50 border-green-500/50 border-purple-500/50 border-orange-500/50
                    border-slate-500/50 border-gray-500/50 border-pink-500/50 border-cyan-500/50
                    border-violet-500/50 border-indigo-500/50 border-fuchsia-500/50
                    hover:border-primary-500/80 hover:border-secondary-500/80 hover:border-blue-500/80 hover:border-red-500/80
                    hover:border-yellow-500/80 hover:border-green-500/80 hover:border-purple-500/80 hover:border-orange-500/80
                    hover:border-slate-500/80 hover:border-gray-500/80 hover:border-pink-500/80 hover:border-cyan-500/80
                    hover:border-violet-500/80 hover:border-indigo-500/80 hover:border-fuchsia-500/80
                    hover:border-primary-600 hover:border-secondary-600 hover:border-blue-600 hover:border-red-600
                    hover:border-yellow-600 hover:border-green-600 hover:border-purple-600 hover:border-orange-600
                    hover:border-slate-600 hover:border-gray-600 hover:border-pink-600 hover:border-cyan-600
                    hover:border-violet-600 hover:border-indigo-600 hover:border-fuchsia-600
                    active:border-primary-600 active:border-secondary-600 active:border-blue-600 active:border-red-600
                    active:border-yellow-600 active:border-green-600 active:border-purple-600 active:border-orange-600
                    active:border-slate-600 active:border-gray-600 active:border-pink-600 active:border-cyan-600
                    active:border-violet-600 active:border-indigo-600 active:border-fuchsia-600
                    focus:border-primary-500`,

            rings: `ring-1 ring-2 ring-4 ring-8 focus:ring-1 focus:ring-2 focus:ring-4 focus:ring-8 focus:ring-black
                    ring-primary-200 ring-blue-200 ring-red-200 ring-yellow-200
                    ring-green-200 ring-purple-200 ring-orange-200 ring-slate-200
                    ring-gray-200 ring-pink-200 ring-cyan-200 ring-violet-200
                    ring-indigo-200 ring-fuchsia-200
                    focus:ring-primary-500 focus:ring-blue-500 focus:ring-red-500 focus:ring-yellow-500
                    focus:ring-green-500 focus:ring-purple-500 focus:ring-orange-500 focus:ring-slate-500
                    focus:ring-gray-500 focus:ring-pink-500 focus:ring-cyan-500 focus:ring-violet-500
                    focus:ring-indigo-500 focus:ring-fuchsia-500`,
        },
        sizes: `w-6 w-7 w-10 w-11 w-14 w-16 w-24 w-36 h-6 h-7 h-10 h-11 h-14 h-16 h-24 h-36 size-6 size-7 size-10 size-11 size-14 size-16 sm:w-1/6 
                sm:w-1/5 sm:w-1/4 sm:w-1/3 sm:w-2/5 sm:w-2/3 sm:w-11/12 lg:w-1/6 lg:w-1/5 lg:w-1/4 lg:w-1/3 
                lg:w-2/5 lg:w-2/3 lg:w-11/12`,
        positions: "-bottom-1 -top-1",
        radius: "rounded-full rounded-lg rounded-xl rounded-2xl rounded-3xl rounded-b-lg rounded-b-xl rounded-b-2xl rounded-b-3xl",
        // dark counterparts for the faint alert shade, which had none and so stayed
        // pale on a dark page. built by interpolation in alert.blade.php, which is
        // why they have to be listed here for Tailwind to see them. See #598
        alertDark: `dark:bg-primary-500/15 dark:bg-blue-500/15 dark:bg-red-500/15 dark:bg-yellow-500/15 dark:bg-green-500/15 dark:bg-orange-500/15 dark:bg-purple-500/15 dark:bg-cyan-500/15 dark:bg-pink-500/15 dark:bg-gray-500/15 dark:bg-slate-500/15 dark:bg-violet-500/15 dark:bg-indigo-500/15 dark:bg-fuchsia-500/15 dark:bg-amber-500/15
                dark:text-primary-300 dark:text-blue-300 dark:text-red-300 dark:text-yellow-300 dark:text-green-300 dark:text-orange-300 dark:text-purple-300 dark:text-cyan-300 dark:text-pink-300 dark:text-gray-300 dark:text-slate-300 dark:text-violet-300 dark:text-indigo-300 dark:text-fuchsia-300 dark:text-amber-300
                dark:text-primary-400 dark:text-blue-400 dark:text-red-400 dark:text-yellow-400 dark:text-green-400 dark:text-orange-400 dark:text-purple-400 dark:text-cyan-400 dark:text-pink-400 dark:text-gray-400 dark:text-slate-400 dark:text-violet-400 dark:text-indigo-400 dark:text-fuchsia-400 dark:text-amber-400`,
        opacity: `opacity-0 opacity-5 opacity-10 opacity-20 opacity-25 opacity-30 opacity-40 opacity-50 
                opacity-60 opacity-70 opacity-75 opacity-80 opacity-90 opacity-95 opacity-100`,
    }