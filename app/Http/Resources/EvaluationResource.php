<?php

namespace App\Http\Resources;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Evaluation
 */
class EvaluationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->nama_penilaian,
            'kategori' => $this->jenis,
            'tanggal' => $this->tanggal,
            'rincian_nilai' => $this->whenLoaded('details', function () {
                return $this->details->map(fn ($d) => [
                    'nama_siswa' => $d->student->nama,
                    'skor' => $d->nilai,
                ]);
            }),
        ];
    }
}
