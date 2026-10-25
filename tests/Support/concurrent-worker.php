<?php

use App\Models\Yarn;
use App\Modules\Dyeing\DyeingService;
use App\Modules\Knitting\KnittingService;
use App\Modules\Knitting\LotGenerator;
use App\Support\CuttingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
[$script, $mode, $encoded, $gate] = $argv;
$data = json_decode(base64_decode($encoded), true, flags: JSON_THROW_ON_ERROR);
$started = time();
while (! is_file($gate)) {
    if (time() - $started > 30) {
        exit(3);
    }
    usleep(10000);
}
try {
    $result = match ($mode) {
        'lot' => app(LotGenerator::class)->create(Yarn::findOrFail($data['yarn_id']), $data['date']),
        'knitting' => app(KnittingService::class)->save($data),
        'dyeing' => app(DyeingService::class)->save($data),
        'neck' => app(CuttingService::class)->save($data, 'neck'),
    };
    echo json_encode(['id' => $result->id, 'lot_number' => $result->lot_number]);
} catch (ValidationException $exception) {
    echo json_encode(['errors' => $exception->errors()]);
    exit(2);
}
