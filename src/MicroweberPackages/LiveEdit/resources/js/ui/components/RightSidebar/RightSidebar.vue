<template>
    <div v-if="isReady">


        <div id="general-theme-settings" :class="[showSidebar == true ? 'active' : '']">
            <div>
                <div class="d-flex align-items-center justify-content-between px-3 pt-4 pb-0 position-relative">
                    <h3 v-show="showTemplateSettings" class="fs-2 font-weight-bold">Template Style Editor</h3>

                    <span v-show="!showElementStyleEditor" v-on:click="closeSidebar"
                          :class="[buttonIsActive?'live-edit-right-sidebar-active':'']"
                          class="mdi mdi-close x-close-modal-link" style="top: 17px;"></span>

<!--                    <div id="rightSidebarTabStyleEditorNav" role="tablist">-->
<!--                        <a class="mw-admin-action-links mw-adm-liveedit-tabs active me-3" data-bs-toggle="tab"-->
<!--                           v-on:click="closeElementStyleEditorIfOpened"-->
<!--                           data-bs-target="#style-edit-global-template-settings-holder" type="button" role="tab">-->
<!--                            Template Styles-->
<!--                        </a>-->

<!--                        <a class="mw-admin-action-links mw-adm-liveedit-tabs" data-bs-toggle="tab"-->
<!--                           v-on:click="closeElementStyleEditorIfOpened"-->
<!--                           data-bs-target="#style-edit-custom-template-settings-holder" type="button" role="tab">-->
<!--                            Tools-->
<!--                        </a>-->
<!--                    </div>-->
                </div>

                <div class="tab-content" v-show="showTemplateSettings">
                    <div class="tab-pane active tab-pane-slide-right" id="style-edit-global-template-settings-holder"
                         role="tabpanel">
                        <div>

                            <iframe :src="buildIframeUrlTemplateSettings()" style="width:100%;height:100vh;"
                                    frameborder="0"
                                    allowfullscreen></iframe>

                        </div>

                    </div>
<!--                    <div class="tab-pane tab-pane-slide-right" id="style-edit-custom-template-settings-holder"-->
<!--                         role="tabpanel" style="display: none;">-->

<!--                        <ToolsButtons></ToolsButtons>-->


<!--                    </div>-->
                </div>

                <div class="tab-content" v-show="showElementStyleEditor">


                    <StyleEditor></StyleEditor>
                </div>

            </div>
        </div>
    </div>
</template>

<style>
.general-theme-settings {
    background: #000;

}

.live-edit-gui-editor-opened #live-editor-frame {
    margin-right: 303px;
}
</style>

<script>
import TemplateSettings from "./TemplateSettings/TemplateSettings.vue";
import Editor from "../Toolbar/Editor.vue";
import ToolsButtons from "./ToolsButtons.vue";
import StyleEditor from "../StyleEditor/StyleEditor.vue";

import CSSGUIService from "../../../api-core/services/services/css-gui.service.js";

export default {
    components: {

        StyleEditor,
        Editor,
        ToolsButtons,
        TemplateSettings,
    },
    methods: {
        show: function (name) {
            this.showSidebar = true;
            CSSGUIService.show();
        },
        closeSidebar() {
            this.showTemplateSettings = false;
            this.showSidebar = false;
            this.showElementStyleEditor = false;
            this.buttonIsActive = false;

            CSSGUIService.hide();

        },
        closeElementStyleEditorIfOpened() {
            this.emitter.emit("live-edit-ui-show", 'template-settings');

        },
        openSidebar() {
            this.showTemplateSettings = true;
            this.showSidebar = true;
            this.showElementStyleEditor = false;
            CSSGUIService.show();
        },
        buildIframeUrlTemplateSettings: function (url) {

            var moduleType = 'editor/sidebar_template_settings';
            var attrsForSettings = {};

            attrsForSettings.live_edit = true;
            attrsForSettings.module_settings = true;
            attrsForSettings.id = 'mw_global_sidebar_template_settings';
            attrsForSettings.type = moduleType;
            attrsForSettings.iframe = true;
            attrsForSettings.from_url = mw.app.canvas.getWindow().location.href;

            var src = route('live_edit.module_settings') + "?" + json2url(attrsForSettings);

            return src;

        }
    },
    mounted() {

        const rightSidebarInstance = this;


        mw.app.canvas.on('liveEditCanvasLoaded', () => {


            if (rightSidebarInstance.buttonIsActive) {
                rightSidebarInstance.showTemplateSettings = true;
            }
            this.isReady = true;
        });

        mw.app.canvas.on('liveEditCanvasBeforeUnload', function () {
            rightSidebarInstance.showTemplateSettings = false;
        });

        this.emitter.on("live-edit-ui-show", show => {

            //   rightSidebarInstance.showTemplateSettings = false;
            //  rightSidebarInstance.showElementStyleEditor = false;

            if (show === 'template-settings') {
                rightSidebarInstance.buttonIsActive = true;
                rightSidebarInstance.showTemplateSettings = true;
                rightSidebarInstance.showElementStyleEditor = false;
                rightSidebarInstance.showSidebar = true;

            } else if (show === 'html-editor' || show === 'show-code-editor') {
                rightSidebarInstance.showTemplateSettings = false;
                rightSidebarInstance.showElementStyleEditor = false;
                rightSidebarInstance.showSidebar = false;
                rightSidebarInstance.buttonIsActive = false;
            } else if (show === 'style-editor') {

                rightSidebarInstance.showTemplateSettings = false;
                rightSidebarInstance.showElementStyleEditor = true;
                rightSidebarInstance.showSidebar = true;
                rightSidebarInstance.buttonIsActive = false;


            } else if (show == 'close-element-style-editor') {


                rightSidebarInstance.showTemplateSettings = false;
                rightSidebarInstance.showElementStyleEditor = false;
                rightSidebarInstance.showSidebar = false;
                rightSidebarInstance.buttonIsActive = false;
                rightSidebarInstance.closeSidebar()




            } else {

                rightSidebarInstance.showTemplateSettings = false;
                rightSidebarInstance.showElementStyleEditor = false;
                rightSidebarInstance.showSidebar = false;
                rightSidebarInstance.buttonIsActive = false;
            }

        });

    },
    data() {
        return {
            showSidebar: false,
            showTemplateSettings: false,
            buttonIsActive: false,
            isReady: false,
            showElementStyleEditor: false
        }
    }
}
</script>

