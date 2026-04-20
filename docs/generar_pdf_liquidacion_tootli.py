#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Genera PDF de ejemplos de liquidación Tootli Direct (3 métodos)."""

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle

OUT = __file__.replace("generar_pdf_liquidacion_tootli.py", "tootli-direct-liquidacion-tres-metodos.pdf")


def main():
    styles = getSampleStyleSheet()
    title = ParagraphStyle(
        name="Title",
        parent=styles["Heading1"],
        fontSize=16,
        spaceAfter=12,
        textColor=colors.HexColor("#1a1a1a"),
    )
    h2 = ParagraphStyle(
        name="H2",
        parent=styles["Heading2"],
        fontSize=12,
        spaceBefore=14,
        spaceAfter=8,
        textColor=colors.HexColor("#0d47a1"),
    )
    body = ParagraphStyle(
        name="Body",
        parent=styles["Normal"],
        fontSize=10,
        leading=14,
        spaceAfter=6,
    )
    small = ParagraphStyle(
        name="Small",
        parent=styles["Normal"],
        fontSize=8,
        textColor=colors.grey,
    )

    story = []

    story.append(Paragraph("Liquidación Tootli Direct — Domicilio", title))
    story.append(
        Paragraph(
            "Ejemplos numéricos alineados con la lógica de <b>OrderLogic::create_transaction</b> "
            "(efectivo contra entrega, tarjeta en entrega, pagado en restaurante). "
            "Los porcentajes de comisión sobre envío se configuran en el panel (clave "
            "<i>tootli_direct_delivery_commission</i>).",
            body,
        )
    )
    story.append(Spacer(1, 0.4 * cm))

    # --- 1 ---
    story.append(Paragraph("1. Efectivo contra entrega (<b>cash_on_delivery</b>)", h2))
    data1 = [
        ["Concepto", "Monto"],
        ["Subtotal comida (ejemplo)", "$350.00"],
        ["Envío", "$50.00"],
        ["Total pedido (<i>order_amount</i>)", "$400.00"],
        ["Repartidor cobra en efectivo", "$400.00"],
        ["Comisión Tootli sobre envío (ej. 20% de $50)", "$10.00"],
        ["Parte repartidor del envío (80% de $50)", "$40.00"],
        ["Liquidación efectivo (rest. + comisión envío)", "$350 + $10 = $360"],
        ["Abono billetera restaurante (Tootli)", "<b>+$350.00</b>"],
    ]
    t1 = Table(data1, colWidths=[9 * cm, 5 * cm])
    t1.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e3f2fd")),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("FONTSIZE", (0, 0), (-1, -1), 9),
                ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
            ]
        )
    )
    story.append(t1)
    story.append(
        Paragraph(
            "En sistema: <i>store_amount</i> = order_amount − delivery_charge. "
            "Comisión plataforma sobre comida no se suma aparte en este flujo; "
            "sí la comisión sobre el envío.",
            body,
        )
    )

    # --- 2 ---
    story.append(Paragraph("2. Tarjeta en entrega (<b>card_tootli_direct</b>)", h2))
    data2 = [
        ["Concepto", "Monto"],
        ["Total cobrado (bruto, terminal Tootli)", "$400.00"],
        ["Neto después de fee 3.5% + IVA sobre el fee (~)", "$383.30"],
        ["Envío cobrado al cliente", "$50.00"],
        ["Abono billetera restaurante (neto − envío)", "<b>+$333.30</b>"],
        ["Comisión admin", "Fee tarjeta (bruto − neto) + comisión sobre envío"],
    ]
    t2 = Table(data2, colWidths=[9 * cm, 5 * cm])
    t2.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e8f5e9")),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("FONTSIZE", (0, 0), (-1, -1), 9),
                ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
            ]
        )
    )
    story.append(t2)
    story.append(
        Paragraph(
            "El fee de tarjeta lo absorbe el restaurante: se usa <i>card_net_amount</i> "
            "guardado en <i>pos_payment_meta</i>. El cobro digital registrado en admin usa el neto, no el bruto.",
            body,
        )
    )

    # --- 3 ---
    story.append(Paragraph("3. Pagado en restaurante (<b>paid_at_restaurant</b>)", h2))
    data3 = [
        ["Concepto", "Monto"],
        ["Cobro en tienda (ejemplo)", "$500.00"],
        ["Desglose ilustrativo", "$450 comida + $50 envío"],
        ["Abono por comida en billetera Tootli", "$0.00 (no aplica)"],
        ["Descuento billetera local (envío para reparto)", "<b>−$50.00</b>"],
        ["Comisión Tootli sobre envío", "Según panel"],
        ["Repartidor (billetera)", "Envío − comisión Tootli"],
    ]
    t3 = Table(data3, colWidths=[9 * cm, 5 * cm])
    t3.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#fff3e0")),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("FONTSIZE", (0, 0), (-1, -1), 9),
                ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
            ]
        )
    )
    story.append(t3)
    story.append(
        Paragraph(
            "La comida ya está cobrada fuera del flujo Tootli: <i>store_amount</i> = 0 en la liquidación. "
            "Solo se incrementa <i>total_withdrawn</i> por el monto del envío (reparto por plataforma, no auto-entrega).",
            body,
        )
    )

    story.append(Spacer(1, 0.6 * cm))
    story.append(
        Paragraph(
            "Documento generado para uso interno. Verificar comisiones y montos reales en cada orden.",
            small,
        )
    )

    doc = SimpleDocTemplate(
        OUT,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
    )
    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    main()
