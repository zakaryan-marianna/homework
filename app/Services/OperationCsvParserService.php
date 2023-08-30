<?php

namespace App\Services;

use App\Enums\OperationType;
use App\Enums\UserType;
use App\Exceptions\FileNotFoundException;
use App\Objects\Operation;
use App\Rules\CurrencyRule;
use App\Rules\OperationTypeRule;
use App\Rules\UserTypeRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OperationCsvParserService
{
    /**
     * Template of the CSV file.
     */
    private const TEMPLATE = ['date', 'user_id', 'user_type', 'operation_type', 'amount', 'currency'];

    /**
     * Stream resource of the CSV file.
     *
     * @var resource $resource
     */
    private $resource;

    /**
     * Constructor.
     *
     * @param string $filename
     * @throws FileNotFoundException
     */
    public function __construct(string $filename)
    {
        $this->readFile($filename);
    }

    /**
     * Get "next" operation from the CSV file.
     *
     * @return Operation|null
     * @throws ValidationException
     */
    public function getOperation(): ?Operation
    {
        $data = $this->parseCsvLine();
        if ($data === null) {
            return null;
        }
        $this->validate($data);

        $date = Carbon::parse($data['date']);
        $userId = (int)$data['user_id'];
        $userType = UserType::from($data['user_type']);
        $operationType = OperationType::from($data['operation_type']);
        $amount = (float)$data['amount'];
        $currency = $data['currency'];

        return new Operation($date, $userId, $userType, $operationType, $amount, $currency);
    }

    /**
     * Read file stream.
     *
     * @param string $filename
     * @return void
     * @throws FileNotFoundException
     */
    private function readFile(string $filename): void
    {
        if (!Storage::exists($filename)) {
            throw new FileNotFoundException("$filename does not exist.");
        }
        $this->resource = Storage::readStream($filename);
    }

    /**
     * Parse "next" CSV line into associative array.
     *
     * @return array|null
     */
    private function parseCsvLine(): ?array
    {
        $rawData = fgetcsv($this->resource);
        if ($rawData === false) {
            return null;
        }
        $result = [];
        foreach (self::TEMPLATE as $key => $column) {
            $result[$column] = $rawData[$key] ?? null;
        }

        return $result;
    }

    /**
     * Validate the operation parameters.
     *
     * @param $data
     * @return void
     * @throws ValidationException
     */
    private function validate($data): void
    {
        $validator = Validator::make($data, [
            'date' => ['required', 'date'],
            'user_id' => ['required', 'integer'],
            'user_type' => ['required', new UserTypeRule()],
            'operation_type' => ['required', new OperationTypeRule()],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', new CurrencyRule()]
        ]);

        $validator->validate();
    }
}
