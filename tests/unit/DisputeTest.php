<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class DisputeTest extends Setup
{
    private $attributes;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributes = [
            'amount' => '100.00',
            'amountDisputed' => '100.00',
            'amountWon' => '0.00',
            'caseNumber' => 'CB123456',
            'chargebackProtectionLevel' => 'effortless',
            'createdAt' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            'currencyIsoCode' => 'USD',
            'dateOpened' => DateTime::createFromFormat('Ymd-His', '20130401-000000'),
            'dateWon' => DateTime::createFromFormat('Ymd-His', '20130402-000000'),
            'processorComments' => 'Forwarded comments',
            'id' => '123456',
            'kind' => 'chargeback',
            'merchantAccountId' => 'abc123',
            'originalDisputeId' => 'original_dispute_id',
            'reason' => 'fraud',
            'reasonCode' => '83',
            'reasonDescription' => 'Reason code 83 description',
            'receivedDate' => DateTime::createFromFormat('Ymd-His', '20130410-000410'),
            'referenceNumber' => '123456',
            'replyByDate' => DateTime::createFromFormat('Ymd-His', '20130417-0000417'),
            'status' => 'open',
            'updatedAt' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            'evidence' => [[
                'category' => null,
                'comment' => null,
                'createdAt' => DateTime::createFromFormat('Ymd-His', '20130411-105039'),
                'id' => 'evidence1',
                'sentToProcessorAt' => null,
                'sequenceNumber' => null,
                'url' => 'url_of_file_evidence',
            ],[
                'comment' => 'text evidence',
                'createdAt' => DateTime::createFromFormat('Ymd-His', '20130411-105039'),
                'id' => 'evidence2',
                'sentToProcessorAt' => '2009-04-11',
                'url' => null,
            ]],
            'statusHistory' => [[
                'effectiveDate' => '2013-04-10',
                'status' => 'open',
                'timestamp' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            ]],
            'transaction' => [
                'id' => 'transaction_id',
                'amount' => '100.00',
                'createdAt' => DateTime::createFromFormat('Ymd-His', '20130319-105039'),
                'installmentCount' => null,
                'orderId' => null,
                'purchaseOrderNumber' => 'po',
                'paymentInstrumentSubtype' => 'Visa',
            ],
            'paypalMessages' => [[
                'message' => 'message',
                'sender' => 'seller',
                'sentAt' => DateTime::createFromFormat('Ymd-His', '20130410-105039'),
            ]],
        ];
    }

    public function testLegacyConstructor()
    {
        $legacyParams = [
            'transaction' => [
                'id' => 'transaction_id',
                'amount' => '100.00',
            ],
            'id' => '123456',
            'currencyIsoCode' => 'USD',
            'status' => 'open',
            'amount' => '100.00',
            'receivedDate' => DateTime::createFromFormat('Ymd-His', '20130410-000410'),
            'replyByDate' => DateTime::createFromFormat('Ymd-His', '20130421-000421'),
            'reason' => 'fraud',
            'transactionIds' => [
                'asdf', 'qwer'
            ],
            'dateOpened' => DateTime::createFromFormat('Ymd-His', '20130410-000410'),
            'dateWon' => DateTime::createFromFormat('Ymd-His', '20130422-000422'),
            'kind' => 'chargeback'
        ];

        $dispute = Braintree\Dispute::factory($legacyParams);

        $this->assertEquals('123456', $dispute->id);
        $this->assertEquals('100.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Braintree\Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Braintree\Dispute::OPEN, $dispute->status);
        $this->assertEquals('transaction_id', $dispute->transactionDetails->id);
        $this->assertEquals('100.00', $dispute->transactionDetails->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-000410'), $dispute->dateOpened);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130422-000422'), $dispute->dateWon);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $dispute->kind);
    }

    public function testLegacyParamsWithNewAttributes()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals('123456', $dispute->id);
        $this->assertEquals('100.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Braintree\Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Braintree\Dispute::OPEN, $dispute->status);
        $this->assertEquals('transaction_id', $dispute->transactionDetails->id);
        $this->assertEquals('100.00', $dispute->transactionDetails->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130401-000000'), $dispute->dateOpened);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130402-000000'), $dispute->dateWon);
        $this->assertEquals(Braintree\Dispute::CHARGEBACK, $dispute->kind);
    }

    public function testConstructorPopulatesNewFields()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals("100.00", $dispute->amountDisputed);
        $this->assertEquals("0.00", $dispute->amountWon);
        $this->assertEquals("CB123456", $dispute->caseNumber);
        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals("effortless", $dispute->chargebackProtectionLevel);
        $this->assertEquals("Effortless Chargeback Protection tool", $dispute->protectionLevel);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->createdAt);
        $this->assertEquals("Forwarded comments", $dispute->processorComments);
        $this->assertEquals("abc123", $dispute->merchantAccountId);
        $this->assertEquals("original_dispute_id", $dispute->originalDisputeId);
        $this->assertEquals("83", $dispute->reasonCode);
        $this->assertEquals("Reason code 83 description", $dispute->reasonDescription);
        $this->assertEquals("123456", $dispute->referenceNumber);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->updatedAt);
        $this->assertNull($dispute->evidence[0]->comment);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130411-105039'), $dispute->evidence[0]->createdAt);
        $this->assertNull($dispute->evidence[0]->category);
        $this->assertEquals('evidence1', $dispute->evidence[0]->id);
        $this->assertNull($dispute->evidence[0]->sentToProcessorAt);
        $this->assertNull($dispute->evidence[0]->sequenceNumber);
        $this->assertEquals('url_of_file_evidence', $dispute->evidence[0]->url);
        $this->assertEquals('text evidence', $dispute->evidence[1]->comment);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130411-105039'), $dispute->evidence[1]->createdAt);
        $this->assertEquals('evidence2', $dispute->evidence[1]->id);
        $this->assertEquals('2009-04-11', $dispute->evidence[1]->sentToProcessorAt);
        $this->assertNull($dispute->evidence[1]->url);
        $this->assertEquals('message', $dispute->paypalMessages[0]->message);
        $this->assertEquals('seller', $dispute->paypalMessages[0]->sender);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->paypalMessages[0]->sentAt);
        $this->assertEquals('2013-04-10', $dispute->statusHistory[0]->effectiveDate);
        $this->assertEquals('open', $dispute->statusHistory[0]->status);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130410-105039'), $dispute->statusHistory[0]->timestamp);
    }

    public function testConstructorHandleStandardCBPLevel()
    {
        $emptyAttributes = [
            'chargebackProtectionLevel' => 'standard'
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals("standard", $dispute->chargebackProtectionLevel);
        $this->assertEquals("Chargeback Protection tool", $dispute->protectionLevel);
    }

    public function testConstructorHandleNullCBPLevel()
    {
        $emptyAttributes = [
            'chargebackProtectionLevel' => null
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals("", $dispute->chargebackProtectionLevel);
        $this->assertEquals("No Protection", $dispute->protectionLevel);
    }

    public function testConstructorHandleEmptyCBPLevel()
    {
        $emptyAttributes = [
            'chargebackProtectionLevel' => ''
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals("", $dispute->chargebackProtectionLevel);
        $this->assertEquals("No Protection", $dispute->protectionLevel);
    }

    public function testConstructorHandleNotprotectedCBPLevel()
    {
        $emptyAttributes = [
            'chargebackProtectionLevel' => 'not_protected'
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        // NEXT_MAJOR_VERSION Remove this assertion when chargebackProtectionLevel is removed from the SDK
        $this->assertEquals("not_protected", $dispute->chargebackProtectionLevel);
        $this->assertEquals("No Protection", $dispute->protectionLevel);
    }

    public function testConstructorHandlesNullFields()
    {
        $emptyAttributes = [
            'amount' => null,
            'dateOpened' => null,
            'dateWon' => null,
            'evidence' => null,
            'paypalMessages' => null,
            'replyByDate' => null,
            'statusHistory' => null
        ];

        $attrs = array_merge([], $this->attributes, $emptyAttributes);

        $dispute = Braintree\Dispute::factory($attrs);

        $this->assertNull($dispute->amount);
        $this->assertNull($dispute->dateOpened);
        $this->assertNull($dispute->dateWon);
        $this->assertNull($dispute->evidence);
        $this->assertNull($dispute->paypalMessages);
        $this->assertNull($dispute->replyByDate);
        $this->assertNull($dispute->statusHistory);
    }

    public function testConstructorPopulatesTransaction()
    {
        $dispute = Braintree\Dispute::factory($this->attributes);

        $this->assertEquals('transaction_id', $dispute->transaction->id);
        $this->assertEquals('100.00', $dispute->transaction->amount);
        $this->assertEquals(DateTime::createFromFormat('Ymd-His', '20130319-105039'), $dispute->transaction->createdAt);
        $this->assertNull($dispute->transaction->installmentCount);
        $this->assertNull($dispute->transaction->orderId);
        $this->assertEquals('po', $dispute->transaction->purchaseOrderNumber);
        $this->assertEquals('Visa', $dispute->transaction->paymentInstrumentSubtype);
    }

    public function testAcceptNullRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        Braintree\Dispute::accept(null);
    }

    public function testAcceptEmptyIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        Braintree\Dispute::accept(" ");
    }

    public function testAddTextEvidenceEmptyIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        Braintree\Dispute::addTextEvidence(" ", "evidence");
    }

    public function testAddTextEvidenceNullIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        Braintree\Dispute::addTextEvidence(null, "evidence");
    }

    public function testAddTextEvidenceEmptyEvidenceRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'content cannot be blank');

        Braintree\Dispute::addTextEvidence("disputeId", " ");
    }

    public function testAddTextEvidenceNullEvidenceRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'content cannot be blank');

        Braintree\Dispute::addTextEvidence("disputeId", null);
    }

    public function testAddTextEvidenceBlankRequestContentRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'content cannot be blank');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => ' ',
                'category' => 'CARRIER_NAME',
                'sequenceNumber' => '0',
            ]
        );
    }

    public function testAddTextEvidenceNullRequestContentRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'content cannot be blank');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => null,
                'category' => 'CARRIER_NAME',
                'sequenceNumber' => '0',
            ]
        );
    }

    public function testAddTextEvidenceBlankRequestCategoryRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'category cannot be blank');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => 'UPS',
                'category' => '',
                'sequenceNumber' => '0',
            ]
        );
    }

    public function testAddTextEvidenceBlankRequestSequenceNumberRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'sequenceNumber cannot be blank');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => 'UPS',
                'category' => 'CARRIER_NAME',
                'sequenceNumber' => '',
            ]
        );
    }

    public function testAddTextEvidenceNonIntegerNumberRequestSequenceNumberRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'sequenceNumber must be an int');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => 'UPS',
                'category' => 'CARRIER_NAME',
                'sequenceNumber' => '4.5',
            ]
        );
    }

    public function testAddTextEvidenceNonIntegerStringRequestSequenceNumberRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'sequenceNumber must be an int');

        Braintree\Dispute::addTextEvidence(
            "disputeId",
            [
                'content' => 'UPS',
                'category' => 'CARRIER_NAME',
                'sequenceNumber' => 'Blah',
            ]
        );
    }

    public function testAddFileEvidenceEmptyIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        Braintree\Dispute::addFileEvidence(" ", 1);
    }

    public function testAddFileEvidenceNullIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        Braintree\Dispute::addFileEvidence(null, 1);
    }

    public function testAddFileEvidenceEmptyEvidenceRaisesValueException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'document with id " " not found');

        Braintree\Dispute::addFileEvidence("disputeId", " ");
    }

    public function testAddFileEvidenceNullEvidenceRaisesValueException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'document with id "" not found');

        Braintree\Dispute::addFileEvidence("disputeId", null);
    }

    public function testAddFileEvidenceBlankRequestContentRaisesValueException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'document with id " " not found');

        Braintree\Dispute::addFileEvidence(
            "disputeId",
            [
                'documentId' => ' ',
                'category' => 'GENERAL',
            ]
        );
    }

    public function testAddFileEvidenceNullRequestContentRaisesValueException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'document with id "" not found');

        Braintree\Dispute::addFileEvidence(
            "disputeId",
            [
                'documentId' => null,
                'category' => 'GENERAL',
            ]
        );
    }

    public function testAddFileEvidenceBlankRequestCategoryRaisesValueException()
    {
        $this->expectException('InvalidArgumentException', 'category cannot be blank');

        Braintree\Dispute::addFileEvidence(
            "disputeId",
            [
                'documentId' => '123',
                'category' => '',
            ]
        );
    }

    public function testFinalizeNullRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        Braintree\Dispute::finalize(null);
    }

    public function testFinalizeEmptyIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        Braintree\Dispute::finalize(" ");
    }

    public function testFindingNullRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id "" not found');

        Braintree\Dispute::find(null);
    }

    public function testFindingEmptyIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'dispute with id " " not found');

        Braintree\Dispute::find(" ");
    }

    public function testRemoveEvidenceEmptyDisputeIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'evidence with id "evidence" for dispute with id " " not found');

        Braintree\Dispute::removeEvidence(" ", "evidence");
    }

    public function testRemoveEvidenceNullDisputeIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'evidence with id "evidence" for dispute with id "" not found');

        Braintree\Dispute::removeEvidence(null, "evidence");
    }

    public function testRemoveEvidenceEvidenceNullIdRaisesNotFoundException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'evidence with id "" for dispute with id "dispute_id" not found');

        Braintree\Dispute::removeEvidence("dispute_id", null);
    }

    public function testRemoveEvidenceEmptyEvidenceIdRaisesValueException()
    {
        $this->expectException('Braintree\Exception\NotFound', 'evidence with id " " for dispute with id "dispute_id" not found');

        Braintree\Dispute::removeEvidence("dispute_id", " ");
    }
}
