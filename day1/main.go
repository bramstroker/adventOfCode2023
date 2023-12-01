package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
)

func main() {

	reader := bufio.NewReader(os.Stdin)
	fmt.Println("Advent Of Code 2023 - 1")
	fmt.Println("---------------------")
	var numbers [4]int
	for i := 0; i < 4; i++ {
		for {
			fmt.Println(fmt.Sprintf("Provide calibration line #%d", i+1))
			num := requestCalibrationLine(reader)
			if num != 0 {
				numbers[i] = num
				break
			}
		}
	}

	sum := 0
	for _, num := range numbers {
		sum += num
	}
	fmt.Println("Output =", sum)
}

func requestCalibrationLine(reader *bufio.Reader) int {
	line, _, _ := reader.ReadLine()

	var firstNumber byte
	var lastNumber byte
	firstNumberSet := false
	for _, b := range line {
		if !isNumber(b) {
			continue
		}
		lastNumber = b
		if !firstNumberSet {
			firstNumber = b
			firstNumberSet = true
		}
	}

	if firstNumber == byte(0) || lastNumber == byte(0) {
		fmt.Println("You must supply two numbers")
		return 0
	}

	concatenated := []byte{firstNumber}
	concatenated = append(concatenated, lastNumber)

	num, err := strconv.Atoi(string(concatenated))
	if err != nil {
		fmt.Println("Error converting to int:", err)
		return 0
	}
	return num
}

func isNumber(b byte) bool {
	return b >= '0' && b <= '9'
}
