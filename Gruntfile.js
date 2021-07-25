/*
 * grunt-js
 */

module.exports = function (grunt) {
    'use strict';

    var assetsDir = 'oc-includes/assets',
        nodeDir = 'node_modules';

    grunt.initConfig({
        clean: {
            vendors: [assetsDir, 'oc-admin/themes/modern/scss/bootstrap']
        },
        copy: {
            'jquery': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery/dist/jquery.min.js',
                        nodeDir + '/jquery/README.md',
                        nodeDir + '/jquery/LICENSE.txt'
                    ],
                    dest: assetsDir + '/jquery',
                    flatten: true
                }]
            },
            'jquery-migrate': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery-migrate/dist/jquery-migrate.min.js',
                        nodeDir + '/jquery-migrate/README.md',
                        nodeDir + '/jquery-migrate/LICENSE.txt'
                    ],
                    dest: assetsDir + '/jquery-migrate',
                    flatten: true
                }]
            },
            'jquery-ui': {
                files: [
                    {
                        expand: true,
                        src: [
                            nodeDir + '/jquery-ui-dist/*.min.js',
                            nodeDir + '/jquery-ui-dist/*.min.css',
                            nodeDir + '/jquery-ui-dist/README.md',
                            nodeDir + '/jquery-ui-dist/LICENSE.txt'
                        ],
                        dest: assetsDir + '/jquery-ui',
                        flatten: true
                    },
                    {
                        expand: true, src: nodeDir + '/jquery-ui-dist/images/*',
                        dest: assetsDir + '/jquery-ui/images',
                        flatten: true
                    }]
            },
            'jquery-treeview': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery-treeview/jquery.treeview.js',
                        nodeDir + '/jquery-treeview/README.md'
                    ],
                    dest: assetsDir + '/jquery-treeview',
                    flatten: true
                }]
            },
            'jquery-validation': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery-validation/dist/jquery.validate.min.js',
                        nodeDir + '/jquery-validation/dist/additional-methods.min.js',
                        nodeDir + '/jquery-validation/README.md',
                        nodeDir + '/jquery-validation/LICENSE.md'
                    ],
                    dest: assetsDir + '/jquery-validation',
                    flatten: true
                }]
            },
            'jquery-ui-nested': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery-ui-nested/jquery-ui-nested.js',
                        nodeDir + '/jquery-ui-nested/README.md'
                    ],
                    dest: assetsDir + '/jquery-ui-nested',
                    flatten: true
                }]
            },
            'spectrum-colorpicker': {
                files: [
                    {
                        expand: true,
                        src: [
                            nodeDir + '/spectrum-colorpicker/spectrum.js',
                            nodeDir + '/spectrum-colorpicker/README.md',
                            nodeDir + '/spectrum-colorpicker/LICENSE',
                            nodeDir + '/spectrum-colorpicker/spectrum.css'
                        ],
                        dest: assetsDir + '/spectrum-colorpicker',
                        flatten: true
                    }]
            },
            'bootstrap-icons': {
                files: [
                    {
                        expand: true,
                        src: nodeDir + '/bootstrap-icons/LICENSE.md',
                        dest: assetsDir + '/bootstrap-icons',
                        flatten: true
                    },
                    {
                        expand: true,
                        cwd: nodeDir + '/bootstrap-icons/font',
                        src: '**/*',
                        dest: assetsDir + '/bootstrap-icons',
                        flatten: false
                    }]
            },
            'tinymce': {
                files: [{
                    expand: true,
                    cwd: nodeDir + '/tinymce',
                    src: '**/*',
                    dest: assetsDir + '/tinymce',
                    flatten: false
                }]
            },
            'opensans-regular-font': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/npm-font-open-sans/fonts/Regular/OpenSans-Regular.ttf',
                        nodeDir + '/npm-font-open-sans/LICENSE',
                        nodeDir + '/npm-font-open-sans/README.md',
                    ],
                    dest: assetsDir + '/fonts/open-sans',
                    flatten: true
                }]
            },
            'bootstrap': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/bootstrap/dist/css/bootstrap.min.*',
                        nodeDir + '/bootstrap/dist/js/bootstrap.min.*',
                        nodeDir + '/bootstrap/README.md',
                        nodeDir + '/bootstrap/LICENSE'
                    ],
                    dest: assetsDir + '/bootstrap',
                    flatten: true
                }]
            },
            'osclass-legacy': {
                files: [{
                    expand: true,
                    cwd: nodeDir + '/osclass-legacy-assets/src',
                    src: '**/*',
                    dest: assetsDir + '/osclass-legacy/',
                    flatten: false
                }]
            },
            'bootstrap-scss': {
                files: [{
                    expand: true,
                    cwd: nodeDir + '/bootstrap/scss',
                    src: '**/*',
                    dest: 'oc-admin/themes/modern/scss/bootstrap',
                    flatten: false
                }
                ]
            },
        },
        sass: {
            dist: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'oc-admin/themes/modern/css/main.css': 'oc-admin/themes/modern/scss/main.scss'
                }
            }
        }
    });

    grunt.registerTask('createAssetsDir', 'Creates the necessary static assets directory', function () {
        // Create the assets dir when it doesn't exists.
        if (!grunt.file.isDir(assetsDir)) {
            grunt.file.mkdir(assetsDir);

            // Output a success message
            grunt.log.oklns(grunt.template.process(
                'Directory "<%= directory %>" was created successfully.',
                {data: {directory: assetsDir}}
            ));
        }
    });

    grunt.registerTask('default', ['clean', 'createAssetsDir', 'copy', 'sass']);
    grunt.registerTask('compile', ['sass']);
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-sass');
};