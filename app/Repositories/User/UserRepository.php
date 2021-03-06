<?php


namespace App\Repositories\User;

use App\Models\User;
use App\Services\AuthorizingService;
use App\Services\NotifierService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements IUserRepository
{

    protected $autorizingService = null;
    protected $notifierService = null;

    public function __construct(AuthorizingService $authorizingService, NotifierService $notifierService)
    {
        $this->autorizingService = $authorizingService;
        $this->notifierService = $notifierService;
    }

    public function create(Array $userData)
    {
        $newUser = new User($userData);
        $newUser->save();
        return $newUser->toArray();
    }

    public function getType(int $id)
    {
        $user = User::where('id', $id)->first();
        return $user->type;
    }

    public function getBalance(int $id)
    {
        $user = User::where('id', $id)->first();
        return $user->balance;
    }

    public function updateBalances(int $idPayer, int $idPayee, float $amount)
    {

        $payerBalance = $this->getBalance($idPayer);
        $payeeBalance = $this->getBalance($idPayee);

        $querysTransaction = [
            'queryPayer' => 'update users set balance = '.(round($payerBalance, 2) - round($amount, 2)).' where id = '.$idPayer,
            'queryPayee' => 'update users set balance = '.(round($payeeBalance, 2) + round($amount, 2)).' where id = '.$idPayee
        ];

        DB::transaction(function() use($querysTransaction) {
            DB::update($querysTransaction['queryPayer']);
            DB::update($querysTransaction['queryPayee']);
            $this->autorizingService->getAuthorization();
            DB::commit();
        });

        $externalServiceResponse = $this->notifierService->sendNotification();
        $externalServiceResponse->setMessage(config('authorizingServiceMessages.transaction_completed'));
        return $externalServiceResponse;
    }
}
