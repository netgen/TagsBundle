import { NgUiInit, NgUiTreeInit } from '@netgen/admin-ui';

function ngTagsInit() {
  NgUiInit();
  NgUiTreeInit('ng-ui-tree-wrapper');
  NgUiTreeInit('ng-ui-modal-tree', { modal: true });
}

window.addEventListener('load', () => {
  ngTagsInit();
});
