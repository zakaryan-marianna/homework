<?php

namespace App\Console\Commands;

use App\Exceptions\FileNotFoundException;
use App\Services\OperationCsvParserService;
use App\Services\OperationHandlerService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CalculateCommissionsCommand extends Command
{
    /**
     * The Operation Handler Service.
     *
     * @var OperationHandlerService
     */
    private OperationHandlerService $operationHandlerService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:calculate {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates commissions of operations provided in the CSV file.';

    /**
     * Execute the console command.
     *
     * @param OperationHandlerService $operationHandlerService
     * @return int
     */
    public function handle(OperationHandlerService $operationHandlerService): int
    {
        $this->operationHandlerService = $operationHandlerService;

        try {
            $this->handleOperations();
        } catch (FileNotFoundException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        } catch (ValidationException $exception) {
            $error = $exception->validator->errors()->first();
            $this->error("Validation failed with message `$error`.");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Handle an operation.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws ValidationException
     */
    private function handleOperations(): void
    {
        $filename = $this->argument('filename');
        $operationCsvParserService = new OperationCsvParserService($filename);
        while ($operation = $operationCsvParserService->getOperation()) {
            $this->operationHandlerService->calculateCommission($operation);
            $commission = $operation->getFormattedCommission();

            $this->line($commission);
        }
    }
}
