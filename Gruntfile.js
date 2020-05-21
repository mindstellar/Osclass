/*
 * grunt-js
 */

module.exports = function (grunt) {
    'use strict';

    var assetsDir = 'oc-includes/assets',
        nodeDir = 'node_modules';

    grunt.initConfig({
        clean: {
            vendors: [assetsDir]
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
                    dest: assetsDir + '/js/jquery',
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
                    dest: assetsDir + '/js/jquery-migrate',
                    flatten: true
                }]
            },
            'jquery-ui': {
                files: [
                    {
                        expand: true,
                        src: [
                            nodeDir + '/jquery-ui-dist/*.min.js',
                            nodeDir + '/jquery-ui-dist/README.md',
                            nodeDir + '/jquery-ui-dist/LICENSE.txt'
                        ],
                        dest: assetsDir + '/js/jquery-ui',
                        flatten: true
                    },
                    {
                        expand: true, src: [
                            nodeDir + '/jquery-ui-dist/*.css',
                            nodeDir + '/jquery-ui-dist/LICENSE.txt'
                        ],
                        dest: assetsDir + '/css/jquery-ui',
                        flatten: true
                    },
                    {
                        expand: true, src: nodeDir + '/jquery-ui-dist/images/*',
                        dest: assetsDir + '/css/jquery-ui/images',
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
                    dest: assetsDir + '/js/jquery-treeview',
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
                    dest: assetsDir + '/js/jquery-validation',
                    flatten: true
                }]
            },
            'jquery-ui-nested': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/nestedSortable/jquery.mjs.nestedSortable.js',
                        nodeDir + '/nestedSortable/README.md'
                    ],
                    dest: assetsDir + '/js/jquery-ui-nested',
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
                            nodeDir + '/spectrum-colorpicker/LICENSE'
                        ],
                        dest: assetsDir + '/js/spectrum',
                        flatten: true
                    },
                    {
                        expand: true, src: [
                            nodeDir + '/spectrum-colorpicker/spectrum.css'
                        ],
                        dest: assetsDir + '/css/spectrum',
                        flatten: true
                    }
                ]
            },
            'material-icons-font': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/material-design-icons/iconfont/**/*',
                        nodeDir + '/material-design-icons/README.md',
                        nodeDir + '/material-design-icons/LICENSE'
                    ],
                    dest: assetsDir + '/fonts/material-icons',
                    flatten: true
                }]
            },
            'tinymce': {
                files: [{
                    expand: true,
                    cwd: nodeDir+'/tinymce',
                    src: '**/*',
                    dest: assetsDir + '/js/tinymce',
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
            'osclass-legacy': {
                files: [{
                    expand: true,
                    cwd: nodeDir+'/osclass-legacy-assets/src',
                    src: '**/*',
                    dest: assetsDir + '/js/osclass-legacy/',
                    flatten: false
                }]
            }
        },
        less: {
            compile: {
                options: {
                    paths: ['oc-admin/themes/modern/less'],
                    compress: true
                },
                files: {
                    'oc-admin/themes/modern/css/main.css': 'oc-admin/themes/modern/less/main.less'
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

    grunt.registerTask('default', ['clean', 'createAssetsDir', 'copy', 'less']);
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
};