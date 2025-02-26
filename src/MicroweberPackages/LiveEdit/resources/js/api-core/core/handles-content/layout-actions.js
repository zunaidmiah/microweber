import MicroweberBaseClass from "../../services/containers/base-class";
import {ElementManager} from "../classes/element";
import {Confirm} from "../classes/dialog";


export const afterLayoutChange = target => {
    const edit = mw.tools.firstParentOrCurrentWithClass(target, 'edit');
    if(edit) {
        const canHasLayout = edit.dataset.layoutContainer !== undefined;
        if(canHasLayout) {
            if(edit.querySelector('.module-layouts') === null) {

                var ghostLayout = mw.element()

                    .addClass('mw-le-ghost-layout')
                    .addClass('noedit')
                    .addClass('no-typing')
                    .addClass('noelement')
                    .addClass('mw-handle-item-layout-plus')
                    .addClass('mw-handle-item-layout-plus-top')
                    .html(mw.lang('Add Layout'))
                    .css({position: 'static'})
                    .on('mousedown touchstart', e => {
                        mw.app.editor.dispatch('appendLayoutRequestOnBottom', edit);
                        e.preventDefault()
                        e.stopPropagation()
                    });
                    ghostLayout.get(0).__edit = edit;
                edit.appendChild(ghostLayout.get(0))
            } else {
                const ghost = edit.querySelector('.mw-le-ghost-layout');
                if(ghost) {
                    ghost.remove()
                }
            }
        }

    }
}

export class LayoutActions extends MicroweberBaseClass {
    proto = null;

    constructor(proto) {
        super();
        this.proto = proto;

    }

    afterLayoutChange(target) {
        return afterLayoutChange(target)
    }

    cloneLayout(target) {
        mw.app.registerUndoState(target)

        var el = document.createElement('div');
        el.innerHTML = target.outerHTML;
        ElementManager('[id]', el).each(function () {
            this.id = 'le-id-' + new Date().getTime();
        });
        ElementManager(target).after(el.innerHTML);
        var newEl = target.nextElementSibling;
        mw.app.registerChange(target);
        mw.reload_module(newEl, function () {
            mw.top().app.state.record({
                target: mw.tools.firstParentWithClass(target, 'edit'),
                value: parent.innerHTML
            });

            newEl.scrollIntoView({behavior: "smooth", block: "start", inline: "start"});
            mw.app.dispatch('layoutCloned', newEl);
        });
    }

    moveUp(target) {
        mw.app.registerUndoState(target)

        var prev = target.previousElementSibling;
        if (!prev) return;
        var offTarget = target.getBoundingClientRect();
        var offPrev = prev.getBoundingClientRect();
        var to = 0;

        if (offTarget.top > offPrev.top) {
            to = -(offTarget.top - offPrev.top)
        }

        target.classList.add("mw-le-target-to-animate");
        prev.classList.add("mw-le-target-to-animate");

        target.style.transform = 'translateY(' + to + 'px)';
        prev.style.transform = 'translateY(' + (-to) + 'px)';

        setTimeout(() => {
            prev.parentNode.insertBefore(target, prev);
            target.classList.remove("mw-le-target-to-animate");
            prev.classList.remove("mw-le-target-to-animate");
            target.style.transform = '';
            prev.style.transform = '';
            mw.app.registerChange(target);
            target.scrollIntoView({behavior: "smooth", block: "start", inline: "start"});
            this.proto.layoutHandle.set(target, true)

        }, 300);

    }

    moveDown(target) {
        mw.app.registerUndoState(target)

        var prev = target.nextElementSibling;
        if (!prev) return;
        var offTarget = target.getBoundingClientRect();
        var offPrev = prev.getBoundingClientRect();
        var to = 0;

        if (offTarget.top < offPrev.top) {
            to = -(offTarget.top - offPrev.top)
        }

        target.classList.add("mw-le-target-to-animate")
        prev.classList.add("mw-le-target-to-animate")

        target.style.transform = 'translateY(' + to + 'px)';
        prev.style.transform = 'translateY(' + (-to) + 'px)';

        setTimeout(() => {
            prev.parentNode.insertBefore(target, prev.nextSibling);
            target.classList.remove("mw-le-target-to-animate")
            prev.classList.remove("mw-le-target-to-animate")
            target.style.transform = '';
            prev.style.transform = '';
            mw.app.registerChange(target)
            target.scrollIntoView({behavior: "smooth", block: "start", inline: "start"});
            this.proto.layoutHandle.set(target, true)
        }, 300)
    }

    deleteLayout(target) {
        var edit = mw.tools.firstParentWithClass(target, 'edit');

        if (edit) {
            mw.app.registerUndoState(edit)
        }

        Confirm('Are you sure you want to delete this layout?', function () {

            target.remove();
            mw.app.registerChange(edit);
            mw.app.registerUndoState(edit)

            //check if the edit is empty and add comment
            if(edit && edit.innerHTML && edit.innerHTML.trim() === '') {
                edit.innerHTML = '<!-- add content here -->';
            }

            afterLayoutChange(edit)
        })
    }
}
