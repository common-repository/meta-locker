import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';
import { CheckboxControl } from '@wordpress/components';
import { dispatch, select } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';

const MetaLockerSidebarPlugin = () => {
    const meta = select('core/editor').getEditedPostAttribute('meta');
    const [isChecked, setChecked] = useState(meta['metaLockerDisabled']);

    return (
        <PluginDocumentSettingPanel
            name="metaLockerPanel"
            title={__('MetaLocker Settings', 'meta-locker')}
        >
            <CheckboxControl
                label={__('Disable Auto-Insert', 'meta-locker')}
                help={__('Do not hide the content automatically.', 'meta-locker')}
                checked={isChecked}
                onChange={value => {
                    setChecked(value);
                    dispatch('core/editor').editPost({
                        meta: {
                            'metaLockerDisabled': value ? '1' : '',
                        },
                    })
                }}
            />
        </PluginDocumentSettingPanel>
    );
}

registerPlugin('meta-locker-settings', {
    icon: 'visibility',
    render: MetaLockerSidebarPlugin,
});
