<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class IconController extends Controller
{
    // Colores corporativos
    private const NAVY  = [13,  74,  119]; // #0D4A77
    private const WHITE = [255, 255, 255];

    public function serve(Request $request, string $name): void
    {
        $allowed = ['icon-192', 'icon-512', 'icon-maskable'];
        if (!in_array($name, $allowed, true)) {
            Response::abort(404);
        }

        $size     = str_contains($name, '192') ? 192 : 512;
        $maskable = str_contains($name, 'maskable');

        if (!function_exists('imagecreatetruecolor')) {
            // GD no disponible → SVG de fallback
            $this->serveSvgFallback($size);
        }

        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');

        $this->generatePng($size, $maskable);
    }

    /**
     * Genera un PNG con barras de señal (ícono de ISP) usando PHP GD.
     * Maskable: padding interno del 20% para la zona segura de Android.
     */
    private function generatePng(int $size, bool $maskable): never
    {
        $im   = imagecreatetruecolor($size, $size);
        $navy  = imagecolorallocate($im, ...self::NAVY);
        $white = imagecolorallocate($im, ...self::WHITE);

        // Fondo con esquinas redondeadas (aproximado con círculos en las esquinas)
        imagefill($im, 0, 0, $navy);
        if ($maskable) {
            // Para maskable el fondo es full-bleed (sin esquinas redondeadas)
            // El contenido importante debe estar dentro del 80% central
            $this->drawContent($im, $white, $size, (int)($size * 0.2));
        } else {
            $this->drawContent($im, $white, $size, (int)($size * 0.14));
        }

        imagepng($im, null, 6);
        imagedestroy($im);
        exit;
    }

    /**
     * Dibuja las 4 barras de señal WiFi/ISP.
     * $pad = padding desde los bordes donde NO dibujar.
     */
    private function drawContent($im, $white, int $size, int $pad): void
    {
        $numBars  = 4;
        $usable   = $size - 2 * $pad;

        // Dimensiones de las barras
        $gapRatio = 0.35;
        $barW     = (int)($usable / ($numBars + ($numBars - 1) * $gapRatio));
        $gap      = (int)($barW * $gapRatio);
        $totalW   = $numBars * $barW + ($numBars - 1) * $gap;

        // Centrar horizontalmente
        $startX   = $pad + (int)(($usable - $totalW) / 2);
        $bottom   = $size - $pad;
        $maxH     = (int)($usable * 0.80); // altura máxima de la barra más alta

        $heights = [0.30, 0.55, 0.75, 1.00];

        for ($i = 0; $i < $numBars; $i++) {
            $barH = (int)($maxH * $heights[$i]);
            $x1   = $startX + $i * ($barW + $gap);
            $x2   = $x1 + $barW - 1;
            $y1   = $bottom - $barH;
            $y2   = $bottom;

            // Cuerpo de la barra
            imagefilledrectangle($im, $x1, $y1 + (int)($barW / 2), $x2, $y2, $white);

            // Tope redondeado (semicírculo arriba)
            $cx = (int)(($x1 + $x2) / 2);
            $r  = (int)($barW / 2);
            imagefilledellipse($im, $cx, $y1 + $r, $barW, $barW, $white);

            // Base redondeada (semicírculo abajo)
            imagefilledellipse($im, $cx, $y2, $barW, $barW, $white);
        }
    }

    private function serveSvgFallback(int $size): never
    {
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="$size" height="$size" viewBox="0 0 $size $size">
          <rect width="$size" height="$size" fill="#0D4A77"/>
          <rect x="60" y="140" width="40" height="60"  rx="6" fill="white"/>
          <rect x="115" y="100" width="40" height="100" rx="6" fill="white"/>
          <rect x="170" y="70"  width="40" height="130" rx="6" fill="white"/>
          <rect x="225" y="40"  width="40" height="160" rx="6" fill="white"/>
        </svg>
        SVG;
        exit;
    }
}
