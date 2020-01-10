/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 *
 */

pimcore.registerNS('coreshop.provider.gateways.powerpay');
coreshop.provider.gateways.powerpay = Class.create(coreshop.provider.gateways.abstract, {

    getLayout: function (config) {

        var storeEnvironments = new Ext.data.ArrayStore({
            fields: ['environment', 'environmentName'],
            data: [
                ['test', 'Test'],
                ['production', 'Production']
            ]
        }), confirmationMethods = new Ext.data.ArrayStore({
            fields: ['confirmationType', 'confirmationName'],
            data: [
                ['instant', 'Instant'],
                ['shipped', 'After Order has been shipped']
            ]
        });

        var optionalFields = [{
            xtype: 'label',
            anchor: '100%',
            style: 'display:block; padding:5px; background:#f5f5f5; border:1px solid #eee; font-weight: 300;',
            html: 'Parameter Cookbook: not available'
        }];

        return [
            {
                xtype: 'combobox',
                fieldLabel: t('powerpay.config.environment'),
                name: 'gatewayConfig.config.environment',
                value: config.environment ? config.environment : '',
                store: storeEnvironments,
                triggerAction: 'all',
                valueField: 'environment',
                displayField: 'environmentName',
                mode: 'local',
                forceSelection: true,
                selectOnFocus: true
            },
            {
                xtype: 'textfield',
                fieldLabel: t('powerpay.config.username'),
                name: 'gatewayConfig.config.username',
                length: 255,
                value: config.username ? config.username : ''
            },
            {
                xtype: 'textfield',
                fieldLabel: t('powerpay.config.password'),
                name: 'gatewayConfig.config.password',
                length: 255,
                value: config.password ? config.password : ''
            },
            {
                xtype: 'textfield',
                fieldLabel: t('powerpay.config.merchant_id'),
                name: 'gatewayConfig.config.merchantId',
                length: 255,
                value: config.merchantId ? config.merchantId : ''
            },
            {
                xtype: 'textfield',
                fieldLabel: t('powerpay.config.filial_id'),
                name: 'gatewayConfig.config.filialId',
                length: 255,
                value: config.filialId ? config.filialId : ''
            },
            {
                xtype: 'textfield',
                fieldLabel: t('powerpay.config.terminal_id'),
                name: 'gatewayConfig.config.terminalId',
                length: 255,
                value: config.terminalId ? config.terminalId : ''
            },
            {
                xtype: 'combobox',
                fieldLabel: t('powerpay.config.confirmation_method'),
                name: 'gatewayConfig.config.confirmationMethod',
                value: config.confirmationMethod ? config.confirmationMethod : '',
                store: confirmationMethods,
                triggerAction: 'all',
                valueField: 'confirmationType',
                displayField: 'confirmationName',
                mode: 'local',
                forceSelection: true,
                selectOnFocus: true
            },
            {
                xtype: 'fieldset',
                title: t('powerpay.config.optional_parameter'),
                collapsible: true,
                collapsed: true,
                autoHeight: true,
                labelWidth: 250,
                anchor: '100%',
                flex: 1,
                defaultType: 'textfield',
                items: optionalFields
            }
        ];
    }
});
