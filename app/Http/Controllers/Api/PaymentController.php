<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Config;
use App\PaymentDetail;
use App\VirtualCurrencyHistory;
use Validator;

class PaymentController extends Controller {
    
    public function __construct() {
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential( Config::get('constant.PAYPAL_CLIENT_ID'), Config::get('constant.PAYPAL_SECRET_KEY') )
        );
    }

    /**
     * Add balance (Using brain tree payment gateway).
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @return Braintree Exception handling.
     * @throws Exception If there was an error
     * @see \App\PaymentDetail
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("amount", description="Amount", type="decimal"),
     *     @Parameter("paymentMethodNonce", description="Nonce to make transaction", type="string"),
     * })
     * @Transaction({
     *     @Request( {"amount": "10","paymentMethodNonce": "fake-vali-nonce"} ),
     *     @Response( {"status": "0","message": "Error Processing Payment.","code": 10001} ),
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function addBalance(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'regex:/\d{0,5}(\.\d{1,2})?/'],
            'paymentMethodNonce' => 'required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => $validator->messages()->all()[0],
                        'code' => '20100'
            ]);
        }
        
        $amount = $request->amount;
        $paymentMethodNonce = $request->paymentMethodNonce;

        try {
            $result = \Braintree_Transaction::sale([
                        'amount' => $amount,
                        'paymentMethodNonce' => $paymentMethodNonce,
                        'options' => [
                            'submitForSettlement' => false
                        ],
                        'customer' => [
                            'email' => $request->user()->email
                        ]
            ]);
            
            $paymentStatus = $this->setUserPaymentDetail($request->user(), $result);
            if ($result->success == 1) {
                $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath.$request->user()->user_pic) : '';
                if($paymentStatus == 1) {
                    \Braintree_Transaction::submitForSettlement($result->transaction->_attributes['id']);
                    return response()->json([
                                'status' => '1',
                                'message' => 'Success',
                                'data' => [
                                    'userDetail' => $request->user()
                                ]
                    ]);
                } else {
                    \Braintree_Transaction::void($result->transaction->_attributes['id']);
                }
            }
            return response()->json([
                        'status' => '0',
                        'message' => 'Error Processing Payment.',
                        'code' => 10001 // Transaction refused because of an invalid argument.
            ]);
        } catch (\Braintree_Exception_NotFound  $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Not found.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Authentication  $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Authentication error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Authorization $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Authorization error. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        }
        catch (\Braintree_Exception_Configuration $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Configuration error. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_DownForMaintenance $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Down for maintanance.',
                        'code' => $e->getCode()
            ]);
        } catch (\Exception\Timeout $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Timeout.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_ForgedQueryString $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Unknown error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_InvalidChallenge $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Invalid format.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_InvalidSignature $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Invalid signature.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_ServerError $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Server error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_SSLCertificate $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Network issue.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Unexpected $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Unexpected error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_TooManyRequests $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'To many request.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_UpgradeRequired $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Upgrade require. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    
    /**
     * To set payment detail and user's point
     * @param [object] $user [User object]
     * @param [object / decimal] $result [Payment success / fail object or amount]
     * @return boolean
     */
    public function setUserPaymentDetail($user, $result) {
        try {
            DB::beginTransaction();
            
            $points = $user->points;
            $transaction_id = null;
            $status = 'fail';
            $walletPoints = 0;
            if(is_object($result)) {
                if ($result->success === true) {
                    $amount = $result->transaction->_attributes['amount'];
                    $walletPoints = round($amount * Config::get('constant.POINTS_PER_USD'));
                    $points = $user->points + $walletPoints;
                    $transaction_id = $result->transaction->_attributes['id'];
                    $status = 'success';
                } else {
                    $transaction_id = (isset($result->_attributes['transaction']->_attributes['id'])) ? $result->_attributes['transaction']->_attributes['id'] : null;
                    $amount = (isset($result->_attributes['params']['transaction']['amount'])) ? $result->_attributes['params']['transaction']['amount'] : 0.00;
                }
            } else {
                $amount = $result;
            }
            $user->fill(array_filter(['points' => $points]));
            $user->save();
            
            // Insert into payment detail
            PaymentDetail::create([
                'user_id'=> $user->id,
                'transaction_id'=> $transaction_id,
                'amount' => $amount,
                'wallet_points' => $walletPoints,
                'status' => $status
            ]);
            DB::commit();
            return 1;
        } catch (Exception $e) {
            DB::rollback();
            return 0;
        }
    }

    
    /**
     * Pay to user (Using PayPal payout service).
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\PaymentDetail
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("points", description="Points", type="Integer"),
     * })
     * @Transaction({
     *     @Request( {"points": "10"} ),
     *     @Response( {"status": "1","message": "Success","data": {"userDetail": {"id": 13,"name": null,"username": "vandit.kotadiya","email": "vishal.shah@inexture.com","phone": "1234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": "nJtmtRQOpkgAO2kNNxR9.png","gender": 1,"points": 4740,"roster_app_amount": null,"funds": null,"is_admin": 0,"social_type": "0","social_id": null,"created_at": "2017-11-01 15:25:35","updated_at": "2018-01-04 14:08:38","deleted_at": null}}} ),
     *     @Response( {"status": "0",'message': 'Connection error.','code' => $e->getCode()} )
     *     @Response( {"status": "0",'message': 'Configuration error.','code' => $e->getCode()} )
     *     @Response( {"status": "0",'message': 'Credential error.','code' => $e->getCode()} )
     *     @Response( {"status": "0",'message': 'Missing credential error.','code' => $e->getCode()} )
     *     @Response( {"status": "0",'message': 'Error while payout. Please try again.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Request is not completed. Please try again','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error while payout. Please try again.','code' => $e->getStatusCode()} )
     * })
     */
    public function payToUser(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'points' => ['required', 'regex:/\d/']
        ]);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => $validator->messages()->all()[0],
                        'code' => '20100'
            ]);
        }
        
        $points = $request->points;
        
        if($request->user()->points < $points) {
            return response()->json([
                        'status' => '0',
                        'message' => 'You can\'t withdraw more than available wallet balance.',
                        'code' => 400
            ]);
        }
        
        $email = 'vishal.shah@inexture.com';
        $withdrawableAmount = number_format((float)( ( $points / Config::get('constant.POINTS_PER_USD') ) * 100 )/ ( 100 + ( 100 * Config::get('constant.PAYOUT_CHARGE_IN_PERCENTAGE') ) ), 2, '.', '');

        // Create a new instance of Payout object
        $payouts = new \PayPal\Api\Payout();
        
        $senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();

        /**
         * --NOTE--
         * You can prevent duplicate batches from being processed.
         * If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.
         * Batch Header Instance
         */
        $senderBatchHeader->setSenderBatchId(uniqid())
            ->setEmailSubject("You have a Payout!");
        
        /**
         * Sender Item
         * Please note that if you are using single payout with sync mode, you can only pass one Item in the request
         */
        $senderItem = new \PayPal\Api\PayoutItem();
        $senderItem->setRecipientType(Config::get('constant.PAYPAL_PAYOUT_RECIPIENT_TYPE'))
            ->setNote('Thanks for your patronage!')
            ->setReceiver($email)
            ->setSenderItemId(uniqid())
            ->setAmount(new \PayPal\Api\Currency('{
                        "value":"'.$withdrawableAmount.'",
                        "currency":"'.Config::get('constant.PAYPAL_PAYOUT_CURRENCY').'"
                    }')); // 
        $payouts->setSenderBatchHeader($senderBatchHeader)
            ->addItem($senderItem);

        // Create Payout
        try {
            $output = $payouts->createSynchronous($this->apiContext);
            
            // Process output
            if($output) {
                $output = is_object($output) ? $output->toArray() : $output;
                
                if(isset($output['items'])) {
                    
                    if( $output['items'][0]['transaction_status']  == Config::get('constant.PAYPAL_PAYOUT_SUCCESS_TRANSACTION_STATUS') ) {
                        $this->setUserPayuotDetail($request->user(), $output, $points, 'success');
                        $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath.$request->user()->user_pic) : '';
                        return response()->json([
                                    'status' => '1',
                                    'message' => 'Success',
                                    'data' => [
                                        'userDetail' => $request->user()
                                    ]
                        ]);
                    } else if ( $output['items'][0]['transaction_status']  == Config::get('constant.PAYPAL_PAYOUT_UNCLAIMED_TRANSACTION_STATUS') ) {
                        
                        $this->setUserPayuotDetail($request->user(), $output, $points, 'fail');
                        
                        // Cancel payout
                        $cancelPayout = \PayPal\Api\PayoutItem::cancel($output['items'][0]['payout_item_id'], $this->apiContext);
                        $cancelPayout = is_object($cancelPayout) ? $cancelPayout->toArray() : $cancelPayout;
                        if($cancelPayout && isset($cancelPayout['transaction_status']) && $cancelPayout['transaction_status'] == Config::get('constant.PAYPAL_PAYOUT_RETURNED_TRANSACTION_STATUS')) {
                            $paymentDetail = PaymentDetail::where('transaction_id', $cancelPayout['transaction_id'])->where('user_id', $request->user()->id)->first();
                            
                            if($paymentDetail != null) {
                                $paymentDetail->fill(array_filter([
                                    'payuot_item_transaction_status' => Config::get('constant.PAYPAL_PAYOUT_RETURNED_TRANSACTION_STATUS')
                                ]));
                                $paymentDetail->save();
                            }
                        }
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Request is not completed. Please try again.',
                                    'code' => 400
                        ]);
                    } else {
                        $this->setUserPayuotDetail($request->user(), $output, $points, 'fail');
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Request is not completed. Please try again.',
                                    'code' => 400
                        ]);
                    }
                }
            } else {
                return response()->json([
                            'status' => '0',
                            'message' => 'Error while payout. Please try again.',
                            'code' => 400
                ]);
            }
        } catch (\PayPal\Exception\PayPalConnectionException $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Connection error.', // $e->getMessage()
                        'code' => $e->getCode()
            ]);
        } catch (\PayPal\Exception\PayPalConfigurationException $e) { 
            return response()->json([
                        'status' => '0',
                        'message' => 'Configuration error.', // $e->getMessage()
                        'code' => $e->getCode()
            ]);
        } catch (\PayPal\Exception\PayPalInvalidCredentialException $e) { 
            return response()->json([
                        'status' => '0',
                        'message' => 'Credential error.', // $e->getMessage()
                        'code' => $e->getCode()
            ]);
        } catch (\PayPal\Exception\PayPalMissingCredentialException $e) { 
            return response()->json([
                        'status' => '0',
                        'message' => 'Missing credential error.', // $e->getMessage()
                        'code' => $e->getCode()
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while payout. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    
    /**
     * 
     * @param [object] $user
     * @param [Array] $payoutOutput
     * @param [decimal] $walletPoint
     * @param [string] $status
     * @return boolean
     */
    public function setUserPayuotDetail($user, $payoutOutput, $walletPoint, $status) {
        try {
            DB::beginTransaction();
            
            $points = ($status == 'success') ? ($user->points - $walletPoint) : $user->points;
            
            $user->fill(array_filter(['points' => $points]));
            $user->save();
            
            // Insert into payment detail
            PaymentDetail::create([
                'user_id'=> $user->id,
                'transaction_id'=> (isset($payoutOutput['items'][0]['transaction_id'])) ? $payoutOutput['items'][0]['transaction_id'] : null,
                'payout_batch_id' => $payoutOutput['items'][0]['payout_batch_id'],
                'payout_item_id' => $payoutOutput['items'][0]['payout_item_id'],
                'wallet_points' => $walletPoint,
                'amount' => $payoutOutput['items'][0]['payout_item']['amount']['value'],
                'status' => $status,
                'payuot_item_transaction_status' => $payoutOutput['items'][0]['transaction_status'],
                'transaction_type' => 'received'
            ]);
            DB::commit();
            return 1;
        } catch (Exception $e) {
            DB::rollback();
            return 0;
        }
    }
    
    /**
     * Payment history of user.
     *
     * @param Request $request The current request
     * @return \App\PaymentDetail A new \App\PaymentDetail object
     * @throws Exception If there was an error
     * @see \App\PaymentDetail
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("points", description="Points", type="Integer"),
     * })
     * @Transaction({
     *     @Request( {"points": "10"} ),
     *     @Response( {"status": "1","message": "Success","data": {"paymentHistory": [{"id": 1,"user_id": 13,"transaction_id": "70d3334g","payout_batch_id": "","payout_item_id": "","wallet_points": 0,"amount": "10.00","status": "success","payuot_item_transaction_status": "","transaction_type": "paid","created_at": "2018-01-02 17:33:41","updated_at": "2018-01-02 17:33:41","deleted_at": null}]}} ),
     *     @Response( {"status": "0",'message': 'Error while feaching payment history. Please try again.','code' => $e->getStatusCode()} )
     * })
     */
    public function paymentHistory(Request $request) {
        try {
            $paymentHistory = $request->user()->paymentDetail()
                    ->where(function($query) {
                        $query->whereNotNull('transaction_id')
                        ->orWhereNotNull('payout_item_id')
                        ->whereNotNull('amount')
                        ->where('amount', '!=', 0.00);
                    })->orderBy('created_at','asc')
                    ->get()->each(function ($paymentHistory) {
                        $paymentHistory->transaction_id = ($paymentHistory->transaction_id !== null) ? $paymentHistory->transaction_id : '';
                        $paymentHistory->payout_batch_id = ($paymentHistory->payout_batch_id !== null) ? $paymentHistory->payout_batch_id : '';
                        $paymentHistory->payout_item_id = ($paymentHistory->payout_item_id !== null) ? $paymentHistory->payout_item_id : '';
                        $paymentHistory->wallet_points = ($paymentHistory->wallet_points !== null) ? $paymentHistory->wallet_points : 0;
                        $paymentHistory->amount = ($paymentHistory->amount !== null) ? $paymentHistory->amount : 0;
                        $paymentHistory->payuot_item_transaction_status = ($paymentHistory->payuot_item_transaction_status !== null) ? $paymentHistory->payuot_item_transaction_status : '';
                    });
            
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'paymentHistory' => $paymentHistory
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while feaching payment history. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    
    
    /** 
     * To generate braintree client token.
     *
     * @return Braintree_ClientToken.
     * @return Braintree Exception handling.
     * @Get("/")
     * @Transaction({
     *     @Request({}),
     *     @Response( {"status": "1","message": "Success","data": {"clientToken": "eyJ2Z"}} )
     *     @Response( {"status": "0",'message': 'Error while generating client token.','code' => $e->getStatusCode()} )
     * })
     */
    public function generateBrainTreeClientToken() {
        try {
            $clientToken = \Braintree_ClientToken::generate();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'clientToken' => $clientToken
                        ]
            ]);
        } catch (\Braintree_Exception_NotFound  $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Not found.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Authentication  $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Authentication error.',
                        'code' => $e->getCode()
            ]);
        } 
        catch (\Braintree_Exception_Authorization $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Authorization error. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Configuration $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Configuration error. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_DownForMaintenance $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Down for maintanance.',
                        'code' => $e->getCode()
            ]);
        } catch (\Exception\Timeout $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Timeout.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_ForgedQueryString $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Unknown error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_InvalidChallenge $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Invalid format.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_InvalidSignature $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Invalid signature.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_ServerError $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Server error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_SSLCertificate $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Network issue.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_Unexpected $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Unexpected error.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_TooManyRequests $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'To many request.',
                        'code' => $e->getCode()
            ]);
        } catch (\Braintree_Exception_UpgradeRequired $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Upgrade require. Please contact admin.',
                        'code' => $e->getCode()
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while generating client token.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Add Virtual curency from wallet balance.
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("points", description="Points", type="Wallet Point to convert into virtual currency"),
     * })
     * @Transaction({
     *     @Request( {"points": "10"} ),
     *     @Response( {"status": "1","message": "Success","data": {"userDetail": {"id": 13,"name": null,"username": "vandit.kotadiya","email": "vishal.shah@inexture.com","phone": "1234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": "http://local.batting-app.com/uploads/user/thumb/nJtmtRQOpkgAO2kNNxR9.png"","gender": 1,"points": 5386,"virtual_currency": 100,"funds": null,"is_admin": 0,"social_type": "0","social_id": null,"notification_status": 0,"status": 0,"created_at": "2017-11-01 15:25:35","updated_at": "2018-03-06 05:15:59","deleted_at": null}}} ),
     *     @Response( {"status": "0","message": "You don\'t have enougth wallet balance.","code": 400 ),
     *     @Response( {"status": "0",'message': 'Error while feaching payment history. Please try again.','code': $e->getStatusCode()} )
     * })
     */
    public function addVirtualCurrency(Request $request) {
        
        try {
            $validator = Validator::make($request->all(), [
                'points' => ['required', 'regex:/\d/']
            ]);

            if ($validator->fails()) {
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            $points = $request->points;

            if($request->user()->points < $points) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have sufficient balance to add Klash Coins. Please add money.',
                            'code' => 400
                ]);
            }
            
            DB::beginTransaction();
            $currency = round($points * Config::get('constant.VIRTUAL_POINTS_PER_WALLET_POINT'));

            $user = $request->user();

            $virtualCurrency = $user->virtual_currency + $currency;
            $walletBalance = $user->points - $points;
            
            $user->fill(array_filter(['virtual_currency' => $virtualCurrency, 'points' => $walletBalance]));
            $user->save();
            //history
            $virtualData = [];
            $virtualData['user_id'] = $request->user()->id;
            $virtualData['points'] = $points;
            $virtualData['virtual_currency'] = $currency;
            $virtualData['status'] = 'credit';

            VirtualCurrencyHistory::create($virtualData);
            //
            DB::commit();
            $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath . $request->user()->user_pic) : '';
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'userDetail' => $request->user()
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Request is not completed. Please try again.',
                        'code' => 400
            ]);
        }
        
    }
    public function getVirtualCurrencyHistory(Request $request) 
    {
        try{
            $user_id = $request->user()->id;
            $userHistory = VirtualCurrencyHistory::leftjoin('gamecase', 'virtual_currency_history.gamecase_id', '=', 'gamecase.id')
                ->leftjoin('gamecase_bundle', 'virtual_currency_history.gamecase_bundle_id', '=', 'gamecase_bundle.id')
                ->where('user_id',$user_id)->get(['virtual_currency_history.*','gamecase.name as gameCaseName', 'gamecase_bundle.name as gameCaseBundleName']);
            
            return response()->json([
                'status' => '1',
                'message' => 'Success',
                'data' => [
                    'userHistory' => $userHistory
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while feaching history. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }

    }
}
