<?php

return array(
    'auth_types' => array(
        'subscriptions'             => 'Subscription',
        'productOrders'             => 'Product',
        'accessMaintenanceOrders'   => 'AMB'
    ),
	'auth_type_locations' => array(
		'subscriptions'             => 'subscriptionsAndOrders',
		'productOrders'             => 'subscriptionsAndOrders',
		'accessMaintenanceOrders'   => 'subscriptionsAndOrders'
	),
    'field_structure' => array(
        'subscriptions' => array(
            'circStatus' => array(
                'path'          => 'circStatus',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'graceFlag' => array(
                'path'            => 'graceFlag',
                'prefix'          => 'subscriptionsAndOrders'
            ),
            'issuesRemaining' => array(
                'path'          => 'issuesRemaining',
                'prefix'        => 'subscriptionsAndOrders'
            ),
	        'deliveryCode' => array(
				'path'          => 'deliveryCode',
		        'prefix'        => 'subscriptionsAndOrders'
	        ),
            'memberCat' => array(
                'path'          => 'memberCat',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'memberOrg' => array(
                'path'          => 'memberOrg',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'renewMethod' => array(
                'path'          => 'renewMethod',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'term' => array(
                'path'          => 'term',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'subType' => array(
                'path'          => 'subType',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemNumber' => array(
                'path'          => 'id.item.itemNumber',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemType' => array(
                'path'          => 'id.item.itemType',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'serviceCode' => array(
                'path'          => 'id.item.serviceCode',
                'prefix'        => 'subscriptionsAndOrders'
            )
        ),
        'productOrders' => array(
            'orderType' => array(
                'path'          => 'orderType',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'orderStatus' => array(
                'path'          => 'orderStatus',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'allowAccess' => array(
                'path'          => 'allowAccess',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemType' => array(
                'path'          => 'item.itemType',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'serviceCode' => array(
                'path'          => 'id.item.serviceCode',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemDescription' => array(
                'path'          => 'id.item.itemDescription',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemNumber' => array(
                'path'          => 'id.item.itemNumber',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'quantityOrdered' => array(
                'path'          => 'quantityOrdered',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'quantityShipped' => array(
                'path'          => 'quantityShipped',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'quantityReturned' => array(
                'path'          => 'quantityReturned',
                'prefix'        => 'subscriptionsAndOrders'
            )
        ),
        'accessMaintenanceOrders' => array(
            'temp' => array(
                 'path'          => 'temp',
                 'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemType' => array(
                'path'          => 'item.itemType',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'serviceCode' => array(
                'path'          => 'id.item.serviceCode',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'itemDescription' => array(
                'path'          => 'id.item.itemDescription',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'hostCode' => array(
                'path'          => 'hostCode',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'startTime' => array(
                'path'          => 'startTime',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'expirationTime' => array(
                'path'          => 'expirationTime',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'affiliateOrderNumber' => array(
                'path'          => 'affiliateOrderNumber',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'expirationDate' => array(
                'path'          => 'expirationDate',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'rateCode' => array(
                'path'          => 'rateCode',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'quantityOrdered' => array(
                'path'          => 'quantityOrdered',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'quantityRemaining' => array(
                'path'          => 'quantityRemaining',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'startDate' => array(
                'path'          => 'startDate',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'termExpirationDate' => array(
                'path'          => 'termExpirationDate',
                'prefix'        => 'subscriptionsAndOrders'
            ),
            'participantStatus' => array(
                'path'          => 'participantStatus',
                'prefix'        => 'subscriptionsAndOrders'
            )
        )
    )
);