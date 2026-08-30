<?php

namespace LisDev\Tests;

use LisDev\Delivery\NovaPoshtaApi2;

/**
 * phpUnit test class.
 *
 * @author lis-dev
 */
class NovaPoshtaApi2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Key for connection.
     *
     * @see https://my.novaposhta.ua/settings/index#apikeys
     */
    private static $key = '';

    /**
     * Test tracking number.
     * TODO: Track number doesn't persists for a long time, so tests can fail due this parameter.
     *
     * @var string
     */
    private $testTrackNumber = '20600009559994';

    /**
     * Instace of tested class.
     *
     * @var NovaPoshtaApi2
     */
    private $np;

    /**
     * Set up before class.
     */
    public static function setUpBeforeClass()
    {
        // Disable notices
        error_reporting(E_ALL ^ E_NOTICE);
        ! self::$key and self::$key = getenv('NOVA_POSHTA_API2_KEY');
    }

    /**
     * Set up before each test.
     */
    protected function setUp()
    {
        // Create new instance
        $this->np = new NovaPoshtaApi2(self::$key);
    }

    /**
     * Test connectin via file_get_contents().
     */
    public function test_set_connection_type()
    {
        $result = $this->np->setConnectionType('file_get_contents');
        $this->assertInstanceOf('LisDev\Delivery\NovaPoshtaApi2', $result);
    }

    /**
     * getConnectionType().
     */
    public function test_get_connection_type()
    {
        $result = $this->np->getConnectionType();
        $this->assertNotEmpty($result);
    }

    /**
     * getKey().
     */
    public function test_get_key()
    {
        $result = $this->np->getKey();
        $this->assertTrue($result != '');
    }

    /**
     * getFormat().
     */
    public function test_get_format()
    {
        $result = $this->np->getFormat();
        $this->assertTrue($result != '');
    }

    /**
     * documentsTracking() result in array.
     */
    public function test_documents_tracking_result_array()
    {
        $result = $this->np->documentsTracking($this->testTrackNumber);

        $this->assertTrue($result['success']);
    }

    /**
     * Test request via file_get_content.
     */
    public function test_request_via_file_get_content()
    {
        $result = $this->np->setConnectionType('file_get_content')->documentsTracking($this->testTrackNumber);
        $this->assertTrue($result['success']);
    }

    /**
     * documentsTracking() result in json.
     */
    public function test_documents_tracking_result_json()
    {
        $result = $this->np->setFormat('json')->documentsTracking($this->testTrackNumber);
        $result = json_decode($result, 1);
        $this->assertTrue($result['success']);
    }

    /**
     * documentsTracking() result in xml.
     */
    public function test_documents_tracking_result_json_xml()
    {
        $result = $this->np->setFormat('xml')->documentsTracking($this->testTrackNumber);
        $result = simplexml_load_string($result);
        $result = json_encode($result);
        $result = json_decode($result, 1);
        $this->assertEquals($result['success'], 'true');
    }

    /**
     * Get cities list by city name.
     *
     * @dataProvider getCitiesData
     */
    public function test_get_cities($cityPage, $cityRef, $cityName)
    {
        $result = $this->np->getCities($cityPage, $cityRef, $cityName);
        $this->assertTrue($result['success']);
    }

    /**
     * Data provider for testGetCities.
     */
    public function getCitiesData()
    {
        return [
            [0, 'Киев', ''],
            [1, '', ''],
            [0, '', 'a9280688-94c0-11e3-b441-0050568002cf'],
        ];
    }

    /**
     * Get warehouses list by city id.
     */
    public function test_get_warehouses()
    {
        $result = $this->np->getWarehouses('a9280688-94c0-11e3-b441-0050568002cf');

        $this->assertTrue($result['success']);
    }

    /**
     * findNearestWarehouse().
     */
    public function test_find_nearest_warehouse()
    {
        $result = $this->np->findNearestWarehouse(['Одесса', 'Донецкая область']);
        $this->assertTrue($result['success']);
    }

    /**
     * getStreet().
     */
    public function test_get_street()
    {
        $result = $this->np->getStreet('a9280688-94c0-11e3-b441-0050568002cf');
        $this->assertTrue($result['success']);
    }

    /**
     * Get Areas.
     *
     * @dataProvider getAreaData
     */
    public function test_get_area($areaName, $areaRef)
    {
        $result = $this->np->getArea($areaName, $areaRef);
        $this->assertTrue($result['success']);
    }

    /**
     * Data provider for testGetArea.
     */
    public function getAreaData()
    {
        return [
            ['Киев', ''],
            ['Чернігівська', ''],
            ['Днепропетровск', ''],
            ['Запорожская', ''],
            ['Одеська', ''],
            ['', '7150813e-9b87-11de-822f-000c2965ae0e'],
            ['', '7150813d-9b87-11de-822f-000c2965ae0e'],
            ['', '71508135-9b87-11de-822f-000c2965ae0e'],
            ['Одеська', '71508135-9b87-11de-822f-000c2965ae0e'],
        ];
    }

    /**
     * Get empty getArea.
     */
    public function test_get_area_empty()
    {
        $result = $this->np->getArea('', '');
        $this->assertFalse($result['success']);
    }

    /**
     * getAreas().
     */
    public function test_get_areas()
    {
        $result = $this->np->getAreas();
        $this->assertTrue($result['success']);
    }

    /**
     * getCity().
     *
     * @dataProvider getCityData
     */
    public function test_get_city($cityName, $regionName)
    {
        $result = $this->np->getCity($cityName, $regionName);
        $this->assertTrue($result['success']);
    }

    /**
     * Data provider for testGetCity.
     */
    public function getCityData()
    {
        return [
            ['Андреевка', 'Запорожье'],
            ['Андреевка', 'Харьковская'],
            ['Мариуполь', 'Донецька'],
            ['Николаев', 'Николаев'],
        ];
    }

    /**
     * Getting language.
     */
    public function test_language_get()
    {
        $language = $this->np->getLanguage();
        $this->assertNotEmpty($language);
    }

    /**
     * Get list of Common model methods.
     *
     * @dataProvider getCommonData
     */
    public function test_get_common($method)
    {
        $result = $this->np->$method();
        $this->assertTrue($result['success']);
    }

    /**
     * Data provider for testGetCommon, returns list of method.
     */
    public function getCommonData()
    {
        return [
            ['getTypesOfCounterparties'],
            ['getBackwardDeliveryCargoTypes'],
            ['getCargoDescriptionList'],
            ['getCargoTypes'],
            ['getDocumentStatuses'],
            ['getOwnershipFormsList'],
            ['getPalletsList'],
            ['getPaymentForms'],
            // Required to sign the agreement
            // array('getTimeIntervals'),
            ['getServiceTypes'],
            ['getTiresWheelsList'],
            ['getTraysList'],
            // Required to sign the agreement
            // array('getTypesOfPayers'),
            ['getTypesOfPayersForRedelivery'],
        ];
    }

    /**
     * Call __call with unregistered method.
     */
    public function test_get_common_error()
    {
        $result = $this->np->someUnregisteredMethod();
        $this->assertEmpty($result);
    }

    /**
     * Save method of Counterparty model.
     */
    public function test_counterparty_save()
    {
        $result = $this->np->model('Counterparty')->save([
            'CounterpartyProperty' => 'Recipient',
            'CityRef' => 'f4890a83-8344-11df-884b-000c290fbeaa',
            'CounterpartyType' => 'PrivatePerson',
            'FirstName' => 'Иван',
            'MiddleName' => 'Иванович',
            'LastName' => 'Иванов',
            'Phone' => '380501112233',
        ]);
        $this->assertTrue($result['success']);

        return $result['data'][0]['Ref'];
    }

    /**
     * Save method of Counterparty model for Organization.
     *
     * TODO Test always is failed with error "Organization does not exists or incorrect EDRPOU'"
     * Uncomment this and all depends when this will be fixed
     */
    public function test_counterparty_organization_save()
    {
        $result = $this->np->model('Counterparty')->save([
            'CounterpartyProperty' => 'Recipient',
            'CityRef' => 'f4890a83-8344-11df-884b-000c290fbeaa',
            'CounterpartyType' => 'Organization',
            'FirstName' => 'ООО Рога и Копыта',
            'MiddleName' => '',
            'LastName' => '',
            'Phone' => '80501112233',
            'OwnershipForm' => '7f0f351d-2519-11df-be9a-000c291af1b3',
            'EDRPOU' => '12345678',
        ]);
        // $this->assertTrue($result['success']);
        /*
        return array(
            'Ref' => $result['data'][0]['Ref'],
            'EDRPOU' => $result['data'][0]['EDRPOU'],
            'CityRef' => $result['data'][0]['CityRef'],
        );
        */
    }

    /**
     * Update for Counterparty model.
     *
     * @depends test_counterparty_save
     */
    public function test_counterparty_update($ref)
    {
        $result = $this->np->model('Counterparty')->update([
            'Ref' => $ref,
            'CounterpartyProperty' => 'Recipient',
            // City code of 'Андреевка (Харьков)'
            'CityRef' => 'a9280688-94c0-11e3-b441-0050568002cf',
            'CounterpartyType' => 'PrivatePerson',
            'FirstName' => 'Петр',
            'MiddleName' => 'Сидорович',
            'LastName' => 'Иванович',
            'Phone' => '380501112234',
        ]);
        $this->assertTrue($result['success']);
    }

    /**
     * Save for ContactPerson model
     * Now PrivatePerson can  create ContactPerson.
     *
     * @depends test_counterparty_save
     */
    public function test_contact_person_save($ref)
    {
        $result = $this->np->model('ContactPerson')->save([
            'CounterpartyRef' => $ref,
            'FirstName' => 'Сидоров',
            'MiddleName' => 'Иванович',
            'LastName' => 'Петров',
            'Phone' => '0501112255',
        ]);

        $this->assertTrue($result['success']);

        return $result['data'][0]['Ref'];
    }

    /**
     * Update for ContactPerson model.
     *
     * @depends test_counterparty_save
     */
    public function test_contact_person_update($counterpartyRef)
    {
        $existedContactPerson = $this->np->getCounterpartyContactPersons($counterpartyRef);
        $result = $this->np->model('ContactPerson')->update([
            'Ref' => $existedContactPerson['data'][0]['Ref'],
            'CounterpartyRef' => $counterpartyRef,
            'FirstName' => 'Петр',
            'MiddleName' => 'Сидорович',
            'LastName' => 'Иванов',
            'Phone' => '0501112266',
        ]);
        $this->assertTrue($result['success']);
    }

    /**
     * Delete for ContactPerson model
     * ContactPerson of natural counterparty can be removed.
     *
     * @depends test_contact_person_save
     */
    public function test_contact_person_delete($ref)
    {
        $result = $this->np->model('ContactPerson')->delete(['Ref' => $ref]);
        // ContactPerson of natural counterparty cannot be removed, so there test assertFalse
        $this->assertTrue($result['success']);
    }

    /**
     * getCounterparties() of Counterparty model.
     *
     * @dataProvider getCounterpartiesData
     */
    public function test_get_counterparties($counterpartyProperty, $page, $findByString, $cityRef)
    {
        $result = $this->np->getCounterparties($counterpartyProperty, $page, $findByString, $cityRef);
        $this->assertTrue($result['success']);
    }

    /**
     * Data for testGetCounterparties().
     */
    public function getCounterpartiesData()
    {
        return [
            ['Sender', '', '', ''],
            ['', 1, '', ''],
            ['', '', 'Иван', ''],
            ['', '', '', 'f4890a83-8344-11df-884b-000c290fbeaa'],
        ];
    }

    /**
     * testGetCounterpartyContactPersons() of Counterparty model.
     *
     * @depends test_counterparty_save
     */
    public function test_get_counterparty_contact_persons($ref)
    {
        $result = $this->np->getCounterpartyContactPersons($ref);
        $this->assertTrue($result['success']);
    }

    /**
     * getCounterpartyOptions() of Counterparty model.
     *
     * @depends test_counterparty_save
     */
    public function test_get_counterparty_options($ref)
    {
        $result = $this->np->getCounterpartyOptions($ref);
        $this->assertTrue($result['success']);
    }

    /**
     * getCounterpartyAddresses() of Counterparty model.
     *
     * @depends test_counterparty_save
     */
    public function test_get_counterparty_addresses($ref)
    {
        $result = $this->np->getCounterpartyAddresses($ref);
        $this->assertTrue($result['success']);
    }

    /**
     * getCounterpartyByEDRPOU() of Counterparty model.
     *
     * TODO Alter test when testCounterpartyOrganizationSave will works correctly
     * -- depends testCounterpartyOrganizationSave
     */
    /*
    public function testGetCounterpartyByEDRPOU()
    {
        $result = $this->np->getCounterpartyByEDRPOU('12345678', 'f4890a83-8344-11df-884b-000c290fbeaa');
         $this->assertEmpty($result['success']);
    }
    */

    /**
     * Delete organization for Counterparty model.
     *
     * @depends testCounterpartyOrganizationSave
    function testCounterpartyOrganizationDelete($params) {
        $result = $this->np->model('Counterparty')->delete(array('Ref' => $params['Ref']));
        $this->assertTrue($result['success']);
    }
     */

    /**
     * Delete for Counterparty model.
     * Counterparty PrivatePerson can't be deleted, so success should be false.
     *
     *
     * @depends test_counterparty_save
     */
    public function test_counterparty_delete($ref)
    {
        $result = $this->np->model('Counterparty')->delete(['Ref' => $ref]);

        $this->assertFalse($result['success']);
    }

    /**
     * cloneLoyaltyCounterpartySender() of Counterparty model.
     */
    public function test_clone_loyalty_counterparty_sender()
    {
        $result = $this->np->cloneLoyaltyCounterpartySender('f4890a83-8344-11df-884b-000c290fbeaa');
        $this->assertTrue($result['success']);

        return $result;
    }

    /**
     * Get the warehouse by city id and description.
     */
    public function test_get_warehouse_many_in_city()
    {
        $result = $this->np->getWarehouse('db5c88d1-391c-11dd-90d9-001a92567626', 'Відділення №1: вул. Маяковського, 59а');

        $this->assertTrue($result['success']);
    }

    /**
     * Get the warehouse by city id and description.
     */
    public function test_get_warehouse_one_in_city()
    {
        $result = $this->np->getWarehouse('db5c88d1-391c-11dd-90d9-001a92567626');

        $this->assertTrue($result['success']);
    }

    /**
     * getDocumentPrice().
     */
    public function test_get_document_price()
    {
        $result = $this->np->getDocumentPrice(
            'db5c88d1-391c-11dd-90d9-001a92567626',
            '8d5a980d-391c-11dd-90d9-001a92567626',
            'WarehouseWarehouse',
            50,
            0.5
        );
        $this->assertTrue($result['success']);
    }

    /**
     * getDocumentDeliveryDate().
     */
    public function test_get_document_delivery_date()
    {
        $result = $this->np->getDocumentDeliveryDate(
            'db5c88d1-391c-11dd-90d9-001a92567626',
            '8d5a980d-391c-11dd-90d9-001a92567626',
            'WarehouseWarehouse',
            date('d.m.Y', time() + 60 * 60 * 4)
        );
        $this->assertTrue($result['success']);
    }

    /**
     * getDocumentList().
     */
    public function test_get_document_list($params = null)
    {
        $result = $this->np->getDocumentList();
        $this->assertTrue($result['success']);

        return $result['data'][0]['Ref'];
    }

    /**
     * generateReport().
     */
    public function test_generate_report()
    {
        // Must return xls with headers
        $result = $this->np->generateReport(['Type' => 'xls', 'DocumentRefs' => ['1fb8943e-14e4-11e5-ad08-005056801333'], 'DateTime' => date('d.m.Y')]);
        $this->assertEmpty($result);
    }

    /**
     * Get first existing sender.
     */
    public function test_new_internet_document_get_sender()
    {
        $existingSender = $this->np->getCounterparties('Sender', 1, '', '');
        $this->assertNotEmpty($existingSender['data'][0]);

        return $existingSender['data'][0];
    }

    /**
     * newInternetDocument().
     *
     * This test must be called much before deleting test to spend
     *  much time to process document on server side of NovaPoshtaAPI
     *
     * @param  array  $sender  Required sender info
     *
     * @depends test_new_internet_document_get_sender
     */
    public function test_new_internet_document($sender)
    {
        $result = $this->np->newInternetDocument(
            [
                'LastName' => $sender['LastName'],
                'FirstName' => $sender['FirstName'],
                'MiddleName' => $sender['MiddleName'],
                'City' => 'Киев',
                'Region' => 'Киевская',
                'Warehouse' => 'Отделение №1: ул. Пироговский путь, 135',
            ],
            [
                'FirstName' => 'Сидор',
                'MiddleName' => 'Сидорович',
                'LastName' => 'Сиродов',
                'Phone' => '0509998877',
                'City' => 'Киев',
                'Region' => 'Киевская',
                'Warehouse' => 'Отделение №3: ул. Калачевская, 13 (Старая Дарница)',
            ],
            [
                'DateTime' => date('d.m.Y', time() + 4 * 84600),
                'ServiceType' => 'WarehouseWarehouse',
                'PaymentMethod' => 'Cash',
                'PayerType' => 'Recipient',
                'Cost' => '500',
                'SeatsAmount' => '1',
                'Description' => 'Спутник',
                'CargoType' => 'Cargo',
                'Weight' => '10',
                'VolumeGeneral' => '0.5',
            ]
        );

        $this->assertTrue($result['success']);

        return $result['data'][0]['Ref'];
    }

    /**
     * getDocument().
     *
     * @depends test_new_internet_document
     */
    public function test_get_document($ref)
    {
        $result = $this->np->getDocument($ref);
        $this->assertTrue($result['success']);
    }

    /**
     * printDocument().
     *
     * @depends test_new_internet_document
     */
    public function test_print_document($ref)
    {
        /*
        There is unexsisted DocumentRef, because if will real id there will not
        any chance delete this tested document
        */
        $result = $this->np->printDocument('123');

        // Code of 'Document not found'
        $this->assertEquals('20000300415', $result['errorCodes'][0]);
    }

    /**
     * printDocument().
     *
     * @depends test_new_internet_document
     */
    public function test_print_document_get_link($ref)
    {
        /*
         There is unexsisted DocumentRef, because if will real id there will not
         any chance delete this tested document
         */
        $result = $this->np->printDocument('123', 'html_link');
        $this->assertTrue($result['success']);
    }

    public function test_print_markings()
    {
        /*
        There is unexsisted DocumentRef, because if will real id there will not
        any chance delete this tested document
        */
        $result = $this->np->printMarkings('123');

        // Code of 'Document does not exist'
        $this->assertEquals('20000202552', $result['errorCodes'][0]);
    }

    public function test_print_markings_get_link()
    {
        /*
         There is unexsisted DocumentRef, because if will real id there will not
         any chance delete this tested document
         */
        $result = $this->np->printMarkings('123', 'html_link');
        $this->assertTrue($result['success']);
    }
}
