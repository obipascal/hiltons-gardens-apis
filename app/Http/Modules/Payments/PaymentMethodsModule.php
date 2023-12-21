<?php namespace App\Http\Modules\Payments;

use App\Models\Payments\PaymentMethods;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

trait PaymentMethodsModule
{
	public function createPayMethod(array $params): bool|null|PaymentMethods
	{
		try {
			if (!$this->payMethodExists($params["account_id"])) {
				$params["pay_method_id"] = random_id();

				if (!$this->__save(new PaymentMethods(), $params)) {
					return false;
				}

				return $this->getPayMethod($params["pay_method_id"]);
			}

			if (!$this->updatePayMethod($params["account_id"], $params)) {
				return false;
			}

			return $this->getPayMethod($params["account_id"]);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function updatePayMethod(string $id, array $params): bool
	{
		try {
			if (!($payMethod = $this->getPayMethod($id))) {
				return false;
			}

			return $this->__update($payMethod, "pay_method_id", $payMethod->pay_method_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function getPayMethod(string $id): bool|null|PaymentMethods
	{
		try {
			return PaymentMethods::query()
				->where("pay_method_id", $id)
				->orWhere("account_id", $id)
				->orWhere("reference", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function allPayMethods(string $user): bool|Collection
	{
		try {
			return PaymentMethods::query()
				->latest()
				->where("account_id", $user)
				->get();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function deletePayMethod(string $id): bool
	{
		try {
			if (!($payMethod = $this->getPayMethod($id))) {
				return false;
			}

			return $this->__delete($payMethod, "pay_method_id", $payMethod->pay_method_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function cardRefExists(string $id): bool
	{
		try {
			return PaymentMethods::query()
				->where("reference", $id)
				->exists();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function payMethodExists(string $id): bool
	{
		try {
			return PaymentMethods::query()
				->where("account_id", $id)
				->exists();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}
