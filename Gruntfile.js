'use strict';

module.exports = function (grunt) {
    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Automatically load required grunt tasks
    require('jit-grunt')(grunt, {
        lockfile: 'grunt-lock'
    });

    // Configurable paths
    var config = {
        sass_dir: 'bundle/Resources/sass/admin',
        public_dir: 'bundle/Resources/public/admin'
    };

    // Define the configuration for all the tasks
    grunt.initConfig({
        // Project settings
        config: config,

        //Prevent multiple grunt instances
        lockfile: {
            grunt: {
                path: 'grunt.lock'
            }
        },

        // Watches files for changes and runs tasks based on the changed files
        watch: {
            gruntfile: {
                files: ['Gruntfile.js'],
                options: {
                    reload: true
                }
            },
            sass: {
                files: ['<%= config.sass_dir %>/{,*/}*.{scss,sass}'],
                tasks: ['sass', 'postcss']
            }
        },

        // Compiles Sass to CSS and generates necessary files if requested
        sass: {
            options: {
                sourceMap: true,
                sourceMapEmbed: true,
                sourceMapContents: true,
                includePaths: ['.']
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= config.sass_dir %>',
                    src: ['*.{scss,sass}'],
                    dest: '.tmp/css',
                    ext: '.css'
                }]
            }
        },

        postcss: {
            options: {
                map: true,
                processors: [
                    // Add vendor prefixed styles
                    require('autoprefixer')({
                        browsers: ['> 1%', 'last 3 versions', 'Firefox ESR', 'Opera 12.1']
                    })
                ]
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: '.tmp/css/',
                    src: '{,*/}*.css',
                    dest: '<%= config.public_dir %>/css'
                }]
            }
        }
    });

    grunt.registerTask('serve', 'Start the server and preview your app', function () {
        grunt.task.run([
            'lockfile',
            'sass:dist',
            'postcss',
            'watch'
        ]);
    });

    grunt.registerTask('default', [
        'serve'
    ]);
};
