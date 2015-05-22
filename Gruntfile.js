module.exports = function(grunt) {

    'use strict';

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // SASS
        sass: {
            options: {
                outputStyle: 'expanded',
                sourceComments: true
            },
            front: {
                files: [
                    {
                        src: 'scss/styles.scss',
                        dest: 'css/styles.css'
                    }
                ]
            },
        },

        // Autoprefix
        autoprefixer: {
            options: {
                browsers: ['last 2 versions', 'ie 8', 'ie 9']
            },
            css: {
                src: 'css/styles.css'
            }
        },

        // Watch
        watch: {
            sass: {
                files: ['scss/styles.scss',],
                tasks: ['sass', 'autoprefixer']
            }
        },

    });

    // Load Npm Tasks

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');


    // Tasks
    grunt.registerTask('dev', ['watch']);
};
