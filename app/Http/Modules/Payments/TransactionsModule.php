<?php namespace App\Http\Modules\Payments;

use App\Http\Modules\Core\BaseModule;
use App\Models\Payments\Transactions;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class TransactionsModule
{
	use BaseModule, PaymentMethodsModule;

	public function create(array $params): bool|null|Transactions
	{
		try {
			$params["trans_id"] = random_id();

			if (!$this->__save(new Transactions(), $params)) {
				return false;
			}

			return $this->get($params["trans_id"]);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($trans = $this->get($id))) {
				return false;
			}

			return $this->__update($trans, "trans_id", $trans->trans_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|Transactions
	{
		try {
			return Transactions::query()
				->where("trans_id", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function all(int $perPage = 50): bool|Paginator
	{
		try {
			return Transactions::query()
				->latest()
				->simplePaginate($perPage);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function delete(string $id): bool
	{
		try {
			if (!($trans = $this->get($id))) {
				return false;
			}

			return $this->__delete($trans, "trans_id", $trans->trans_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function exists(string $id): bool
	{
		try {
			return Transactions::query()
				->where("trans_id", $id)
				->orWhere("reference", $id)
				->exists();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}